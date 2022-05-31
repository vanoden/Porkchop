<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage navigation menus');

	$menuList = new \Navigation\MenuList();
	$menus = $menuList->find();
