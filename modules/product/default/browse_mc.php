<?
	require_once(MODULES."/product/_classes/default.php");
	
	$_product = new Product();
	$_relationship = new ProductRelationship();
	
	if (! $_REQUEST['parent_code'])
	{
		$_REQUEST['parent_code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
	}
	if ($_REQUEST['parent_code'])
	{
		$parent = $_product->get($_REQUEST['parent_code']);
		$_REQUEST['parent_id'] = $parent->id;
	}
	if (! $_REQUEST['parent_id'])
	{
		$_REQUEST['parent_id'] = 0;
		$parent->code = '';
		$parent->name = "Our Products";
	}
	$product_ids = $_relationship->find(array("parent_id" => $_REQUEST['parent_id']));

	$products = array();
	while (list($junk,$relationship) = each($product_ids))
	{
		list($product) = $_product->find(array("id" => $relationship->child_id));
		if (! $product->id) continue;
		array_push($products,$product);
	}
?>
