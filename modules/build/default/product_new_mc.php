<?php
	$page = new \Site\Page();
	$page->requireRole('build user');

	if (isset($_REQUEST['btn_submit'])) {
		$parameters = array(
			'name'	=> $_REQUEST['name'],
			'architecture'	=> $_REQUEST['architecture'],
			'description'	=> $_REQUEST['description'],
			'major_version'	=> $_REQUEST['major_version'],
			'minor_version'	=> $_REQUEST['minor_version'],
			'workspace'		=> $_REQUEST['workspace']
		);
		$product = new \Build\Product();
		if (! $product->add($parameters)) {
			$page->addError($product->error());
		}
		else {
			header("location: /_build/product?id=".$product->id);
			return;
		}
	}
