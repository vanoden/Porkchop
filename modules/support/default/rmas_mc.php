<?
	$page = new \Site\Page();
	$page->requireRole("support user");

	$rmaList = new \Support\Request\Item\RMAList();
	$rmas = $rmaList->find();
?>
