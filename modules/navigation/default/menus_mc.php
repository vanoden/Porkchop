<?php
	$page = new \Site\Page();
	$page->requireRole('content operator');

	$menuList = new \Navigation\MenuList();
	$menus = $menuList->find();
?>
