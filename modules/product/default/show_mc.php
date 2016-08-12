<?
	require_once(MODULES."/product/_classes/default.php");
	require_once(MODULES."/media/_classes/default.php");
	
	$_product = new Product();

	if (! $_REQUEST['code'])
	{
		$_REQUEST['code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
	}
	if ($_REQUEST['code'])
	{
		$product = $_product->get($_REQUEST['code']);
		$_REQUEST['id'] = $product->id;
		$_mediaItem = new MediaDocument();
		$manual = $_mediaItem->details($product->manual_id);
		$_mediaImage = new MediaImage();
		$spectable = $_mediaImage->details($product->spec_table_image);
	}
?>
