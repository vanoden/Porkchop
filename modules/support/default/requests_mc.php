<?
	$page = new \Site\Page();

	$requestList = new \Support\RequestList();
	$requests = $requestList->find();
	if ($requestList->error()) {
		$page->addError($requestList->error());
	}
?>