<?PHP
	$monitor = new Monitor::Asset($_REQUEST["id"]);
	
	require 'XML/Unserializer.php';
    require 'XML/Serializer.php';
    $options = array(
        XML_SERIALIZER_OPTION_INDENT        => '    ',
        XML_SERIALIZER_OPTION_RETURN_RESULT => true,
		XML_SERIALIZER_OPTION_MODE			=> 'simplexml'
    );
    $xml = new XML_Serializer($options);

	$xml->serialize($monitor);
	header('Content-Type: application/xml');
?>
