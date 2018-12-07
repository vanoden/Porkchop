<?PHP
	require 'XML/Unserializer.php';
    require 'XML/Serializer.php';
    $options = array(
        XML_SERIALIZER_OPTION_INDENT        => '    ',
        XML_SERIALIZER_OPTION_RETURN_RESULT => true,
		XML_SERIALIZER_OPTION_MODE			=> 'simplexml'
    );
    $xml = new XML_Serializer($options);

	$monitor = new \Monitor\Asset();
	
	# Get Monitors for Organization
	$monitorList = new \Monitor\AssetList();
	$monitors = $monitorList->find();
	if ($monitorList->error) {
		print $monitorList->error;
		exit;
	}

	$xml->serialize($monitors);
	header('Content-Type: application/xml');
?>
