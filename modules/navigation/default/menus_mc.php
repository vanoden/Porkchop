<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage navigation menus');

	if (!empty($_REQUEST['btn_submit'])) {
		if (empty($_REQUEST['id'])) {
			$menu = new \Site\Navigation\Menu();
			$menu->add(array('code' => $_REQUEST['code'], 'title' => $_REQUEST['title']));
			if ($menu->error()) $page->addError($menu->error());
		}
		else {
			$menu = new \Site\Navigation\Menu($_REQUEST['id']);
			$menu->update(array('code' => $_REQUEST['code'], 'title' => $_REQUEST['title']));
			if ($menu->error()) $page->addError($menu->error());
		}
	}
	$menuList = new \Site\Navigation\MenuList();
	$menus = $menuList->find();
	if ($menuList->error()) $page->addError($menuList->error());