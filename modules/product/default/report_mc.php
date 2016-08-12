<?
	require_once(MODULES."/product/_classes/default.php");
	
	$_product = new Product();

	$products = $_product->find();
?>
