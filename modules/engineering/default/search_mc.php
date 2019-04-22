<?php
    $page = new \Site\Page();
	$page->requireRole('engineering user');
    $page->isSearchResults = true;

    // clean user input and search away
    $searchTerm = preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['search']);
   
    // get any products matched
    $productlist = new \Engineering\ProductList();
    $products = $productlist->find(array('searchTerm'=>$searchTerm));
    if ($productlist->error()) $page->addError($productlist->error());
	
    // get any projects matched
    $projectlist = new \Engineering\ProjectList();
    $projects = $projectlist->find(array('searchTerm'=>$searchTerm));
    if ($projectlist->error()) $page->addError($projectlist->error());
	
    // get any tasks matched
    $taskList = new \Engineering\TaskList();
    $tasks = $taskList->find(array('searchTerm'=>$searchTerm));
    if ($taskList->error()) $page->addError($taskList->error());
