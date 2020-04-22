<?php
	$product = new \Product\Item();

	if (! $_REQUEST['code']) $_REQUEST['code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
	
	if ($_REQUEST['code']) {
		$product->get($_REQUEST['code']);
		$_REQUEST['id'] = $product->id;
		$manual = new \Media\Document($product->manual_id);
		$spectable = new \Media\Image($product->spec_table_image);
	}
