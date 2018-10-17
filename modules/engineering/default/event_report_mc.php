<?
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');

	if (! $_REQUEST['btn_submit']) {
		$_REQUEST['date_start'] = date('m/d/Y',time() - 604800);
	}

	$parameters = array();
	if ($_REQUEST['project_id']) $parameters['project_id'] = $_REQUEST['project_id'];
	if ($_REQUEST['product_id']) $parameters['product_id'] = $_REQUEST['product_id'];
	if ($_REQUEST['user_id']) $parameters['user_id'] = $_REQUEST['user_id'];
	if ($_REQUEST['date_start']) $parameters['date_start'] = $_REQUEST['date_start'];
	if ($_REQUEST['date_end']) $parameters['date_end'] = $_REQUEST['date_end'];
	
	$eventlist = new \Engineering\EventList();
	$events = $eventlist->find($parameters);
	if ($eventlist->error()) $page->addError($eventlist->error());

	$projectlist = new \Engineering\ProjectList();
	$projects = $projectlist->find();
	if ($projectlist->error()) $page->addError($projectlist->error());

	$productlist = new \Engineering\ProductList();
	$products = $productlist->find();
	if ($productlist->error()) $page->addError($productlist->error());

	$userlist = new \Register\CustomerList();
	$users = $userlist->find(array('role' => 'support user'));
	if ($userlist->error) $page->addError($userlist->error);
?>