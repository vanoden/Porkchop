<?
	require_once(MODULES."/media/_classes/default.php");

	# Get Parameters
	if (! $_REQUEST['type'])
		$_REQUEST['type'] = $GLOBALS['_REQUEST_']->query_vars_array[0];

	# Get Documents
	$_item = new MediaItem();
	$items = $_item->find(array("type" => $_REQUEST['type']));

	#print_r($items);
	#exit;
?>
