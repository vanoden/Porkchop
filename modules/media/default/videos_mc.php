<?
	require_once(MODULES."/media/_classes/default.php");

	# Get Documents
	$_item = new MediaItem();
	$items = $_item->find(
		array(
			"type" => "video"
		)
	);

	#print_r($items);
	#exit;
?>