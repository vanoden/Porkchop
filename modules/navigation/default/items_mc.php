<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage navigation menus');
	if ($_REQUEST['parent_id']) {
		$item = new \Navigation\Item($_REQUEST['parent_id']);
	}

	if ($_REQUEST['id']) {
		$menu = new \Navigation\Menu($_REQUEST['id']);
	}
	elseif ($GLOBALS['_REQUEST_']->query_vars_array[0]) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$menu = new \Navigation\Menu();
		$menu->get($code);
	}
	
	if ($_REQUEST['delete']) {
		$ditem = new \Navigation\Item($_REQUEST['delete']);
		$ditem->delete();
	}

	if (isset($item)) {
		$items = $item->children();
		$parent = $item;
	}
	elseif (isset($menu)) {
		$items = $menu->items();
		$parent = new \Navigation\Item();
	}