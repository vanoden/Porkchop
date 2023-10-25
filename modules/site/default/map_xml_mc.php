<?php
	$site = new \Site();
    $page = $site->page();

	$moduleList = $site->module_list();

	$pageList = new \Site\PageList();
	header("Content-Type: application/xml");
