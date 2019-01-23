<?PHP
	require_once MODULES."/monitor/_classes/default.php";
	require 'XML/Unserializer.php';
    require 'XML/Serializer.php';
    $options = array(
        XML_SERIALIZER_OPTION_INDENT        => '    ',
        XML_SERIALIZER_OPTION_RETURN_RESULT => true,
		XML_SERIALIZER_OPTION_MODE			=> 'simplexml'
    );
    $xml = &new XML_Serializer($options);

	$_monitor = new Monitor();
	
	# Get Monitors for Organization
	$monitors = $_monitor->catalog();
	if ($_monitor->error)
	{
		print $_monitor->error;
		exit;
	}

	$xml->serialize($monitors);
	header('Content-Type: application/xml');
?>
