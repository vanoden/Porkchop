<?
	$page = new \Site\Page();

	$parameters = array('status' => array('NEW','OPEN'));
	if ($_REQUEST['btn_all']) $parameters = array();

	$requestList = new \Support\RequestList();
	$requests = $requestList->find($parameters);
	if ($requestList->error()) {
		$page->addError($requestList->error());
	}
?>