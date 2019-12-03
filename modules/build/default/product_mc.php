<?php
	$page = new \Site\Page();
	$page->requireRole('build user');

	$product = new \Build\Product($_REQUEST['id']);

	if ($_REQUEST['btn_submit']) {
		$parameters = array(
			"description"	=> $_REQUEST['description'],
			"major_version"	=> $_REQUEST['major_version'],
			"minor_version" => $_REQUEST['minor_version'],
			"workspace"		=> $_REQUEST['workspace']
		);
		if (! $product->update($parameters)) {
			$page->addError($product->error());
		}
	}
?>