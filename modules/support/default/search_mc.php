<?php
    $page = new \Site\Page();
    $page->isSearchResults = true;
	$page->fromRequest();
	$page->requireRole('support user');

    // clean user input and search away
    $searchTerm = preg_replace("/[^A-Za-z0-9\- ]/", '', $_REQUEST['search']);
    
    // search service items that match
    $supportRequestList = new \Support\RequestList();
    $supportRequestList = $supportRequestList->find(array('searchTerm'=>$searchTerm));
    $supportItemList = new Support\Request\ItemList();
    $supportItemList = $supportItemList->find(array('searchTerm'=>$searchTerm));   
	$actionlist = new \Support\Request\Item\ActionList();
	$actions = $actionlist->find(array('searchTerm'=>$searchTerm));
