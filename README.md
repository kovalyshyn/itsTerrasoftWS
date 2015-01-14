# itsTerrasoftWS
Интеграция веб-приложений (PHP) с TerrasoftCRM 3.x


### Создаем объект и заполняем переменные: 

	$ws = new itsTerrasoftWS();
	$ws->set_Host('http://10.10.10.10:81/crm/TSWebServicesServerLibrary.dll/wsdl/IServer');
	$ws->set_Username('Supervisor');
	$ws->set_MaxPackageSize($ws->get_MaxPackageSize() - 558);
	$ws->set_wsClient();

### Получаем массив с доступными конфигурациями 

	$wsConfigurations = $ws->get_wsConfigurations();

### Устанавливаем Соединение 

	$ws->set_Configuration($wsConfigurations[1]);
	$ws->OpenConfiguration();

### Создаем XML-ый запрос и выполняем 

	$SQL = "select [OfficialAccountName] from tbl_Account where ID = :AccountID ";
	
	$ws->CreateXML($SQL);
	
	$ws->CreateParam();
	$ws->AddDBParam("AccountID", "1", "1", "0", "{DFC26A8C-C284-4FBF-9DE2-39E8D77F1915}");
	
	$ws->ExecuteSQL();

### Получаем XML-ый ответ, выводим и закрываем соединение 

	$xml = DOMDocument::loadXML( $ws->get_XMLResult() );
	$params = $xml->getElementsByTagName('R');
	$k=0;
	foreach ($params as $param)
	{
  	 echo $params->item($k)->getAttribute('F0')."<br>";
  	 $k++;
	}
	$ws->CloseConfiguration();

Библиотека была разработана в 2009 году и не пересматривались с этого момента. 
[Вопросы и замечания](https://github.com/kovalyshyn/itsTerrasoftWS/issues).
