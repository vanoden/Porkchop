<?PHP
	$_monitor = new Monitor();
	
	require 'XML/Unserializer.php';
    require 'XML/Serializer.php';
    $options = array(
        XML_SERIALIZER_OPTION_INDENT        => '    ',
        XML_SERIALIZER_OPTION_RETURN_RESULT => true,
		XML_SERIALIZER_OPTION_MODE			=> 'simplexml'
    );
    $xml = &new XML_Serializer($options);

	# Get Event Information
	$monitor->monitor = $_monitor->details($_REQUEST["id"]);

	//print_r($event);
	if (! $_REQUEST["id"])
	{
	}

	$xml->serialize($monitor);
	header('Content-Type: application/xml');
?>