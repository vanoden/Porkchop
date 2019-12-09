<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');
    $parameters = array();
    
    $firstSearch = (empty($_REQUEST)) ? true : false;
	
    // default to NEW if status isn't picked
    $request = new \Support\Request();
    if (empty($_REQUEST['NEW']) && empty($_REQUEST['CANCELLED']) && empty($_REQUEST['OPEN']) && empty($_REQUEST['CLOSED'])) {
        $_REQUEST['OPEN'] = "OPEN";
        $_REQUEST['NEW'] = "NEW";
    }
    if ($_REQUEST['NEW']) $parameters['status'][] = 'NEW';
    if ($_REQUEST['CANCELLED']) $parameters['status'][] = 'CANCELLED';
    if ($_REQUEST['OPEN']) $parameters['status'][] = 'OPEN';
    if ($_REQUEST['CLOSED']) $parameters['status'][] = 'CLOSED';
    
    // get requests based on search params
	$requestList = new \Support\RequestList();
	if ($requestList->error()) $page->addError($requestList->error());
	if (empty($_REQUEST['min_date'])) $_REQUEST['min_date'] = date('Y-d-m', time());
	if (empty($_REQUEST['max_date'])) $_REQUEST['max_date'] = date('Y-d-m', time());

	$parameters['min_date'] = date('Y-d-m', strtotime($_REQUEST['dateStart']));
	$parameters['max_date'] = date('Y-d-m', strtotime($_REQUEST['dateEnd']));
	$requests = array();
	if (!$firstSearch) $requests = $requestList->find($parameters);

