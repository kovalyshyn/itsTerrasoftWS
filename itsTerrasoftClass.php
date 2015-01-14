<?php
/* 
 *  This Class is free software: you can redistribute it and/or modify
 *  it under the terms of the The MIT License (MIT).
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Terrasoft Web Services Class v.0.2
 * Created: 01/06/2009
 * Updated: 02/06/2009
 * @author Vitaly Kovalyshyn aka samael
 * samael@it-sfera.com.ua
 *
 */

class itsTerrasoftWS {
    
    private $Host;
    private $Username = null;
    private $Password = null;

    private $FetchRecordsCount = -1;
    private $MaxPackageSize = 512000;

    private $wsClient;
    private $wsConfiguration;
    private $wsConfigurationNames;
    private $wsConnection;
    private $PackageSessionID;

    private $XmlDoc;
    private $UseParams = false;
    private $DBParams;
    private $RootNodeName;
    private $XMLMime;
    private $XMLResult;

// Set Methods
    function set_Host( $Host ){
       $this->Host = $Host;
    }
    function set_Username( $Username ){
       $this->Username = $Username;
    }
    function set_Password( $Password ){
       $this->Password = $Password;
    }
    function set_FetchRecordsCount( $FetchRecordsCount ){
       $this->FetchRecordsCount = $FetchRecordsCount;
    }
    function set_MaxPackageSize( $MaxPackageSize ){
       $this->MaxPackageSize = $MaxPackageSize;
    }
    function set_wsClient(){
        $this->wsClient = new SoapClient( $this->Host );
    }
    function set_Configuration ( $Configuration ){
        $this->wsConfiguration = $Configuration;
    }

// Get Methods
    function get_MaxPackageSize(){
       return $this->MaxPackageSize = $MaxPackageSize;
    }
    function get_wsConfigurations(){
        $this->wsConfigurationNames = $this->wsClient->Get_ServerConfigurationNames();
        return explode(";", $this->wsConfigurationNames);
    }
    function get_XMLResult(){
        return $this->XMLResult;
    }

// Open & Close SOAP
    function OpenConfiguration() {
        $this->wsConnection = $this->wsClient->OpenConfiguration($this->wsConfiguration, $this->Username, $this->Password, '0', '', '');
    }
    function CloseConfiguration() {
        $this->wsClient->CloseConfiguration( $this->get_SessionID() );
    }

// XML Methods
    function CreateXML($SQL) {
        $order = array("\r\n", "\n", "\r");
        $SQL = str_replace($order, " ", trim($SQL));

        $this->XmlDoc = new DOMDocument('1.0', 'UTF-8');
        $this->RootNodeName = $this->XmlDoc->createElement("R");

        $SQLCommand = $this->XmlDoc->createAttribute("SQLCommand");
        $SQLCommandValue = $this->XmlDoc->createTextNode($SQL);
        $SQLCommand->appendChild($SQLCommandValue);
        $this->RootNodeName->appendChild($SQLCommand);
    }
    function CreateParam() {
         $this->DBParams = $this->XmlDoc->createElement("DBParams");
         $this->UseParams = true;
    }
    function AddDBParam($Name, $DataType, $ParamType, $IsNull, $Value){
        $DBParam = $this->XmlDoc->createElement("DBParam");

        $DBParamName = $this->XmlDoc->createAttribute("Name");
        $DBParamNameValue = $this->XmlDoc->createTextNode($Name);
        $DBParamName->appendChild($DBParamNameValue);
        $DBParam->appendChild($DBParamName);

        $DBParamDataType = $this->XmlDoc->createAttribute("DataType");
        $DBParamDataTypeValue = $this->XmlDoc->createTextNode($DataType);
        $DBParamDataType->appendChild($DBParamDataTypeValue);
        $DBParam->appendChild($DBParamDataType);
        
        $DBParamParamType = $this->XmlDoc->createAttribute("ParamType");
        $DBParamParamTypeValue = $this->XmlDoc->createTextNode($ParamType);
        $DBParamParamType->appendChild($DBParamParamTypeValue);
        $DBParam->appendChild($DBParamParamType);
        
        $DBParamIsNull = $this->XmlDoc->createAttribute("IsNull");
        $DBParamIsNullValue = $this->XmlDoc->createTextNode($IsNull);
        $DBParamIsNull->appendChild($DBParamIsNullValue);
        $DBParam->appendChild($DBParamIsNull);
        
        $DBParamValue = $this->XmlDoc->createAttribute("Value");
        $DBParamValueValue = $this->XmlDoc->createTextNode($Value);
        $DBParamValue->appendChild($DBParamValueValue);
        $DBParam->appendChild($DBParamValue);
        
        $this->DBParams->appendChild($DBParam);
   }

// Internal Methods
    private function get_SessionID(){
        return $this->wsConnection['ASessionUID'];
    }
    private function XMLToMime() {
        if ($this->UseParams)
            $this->RootNodeName->appendChild($this->DBParams);
        $this->XmlDoc->appendChild($this->RootNodeName);
        $this->XMLMime = base64_encode( mb_convert_encoding (
                   $this->XmlDoc->saveXML() , 'UCS-2LE' ) );
    }
    private function MimeToXML() {
        $this->XMLResult = mb_convert_encoding(base64_decode(
                $this->PackageSessionID ), 'UTF-8', 'UCS-2LE' );
    }
    private function PrepareSendPackages() {
        $this->PackageSessionID = $this->wsClient->PrepareSendPackages( $this->get_SessionID(),
                $this->XMLMime, false, $this->FetchRecordsCount, $this->MaxPackageSize );
    }

 // Execute Methode
    function ExecuteSQL(){
        $this->XMLToMime();
        $this->PrepareSendPackages();
        $this->MimeToXML();
    }

}
?>