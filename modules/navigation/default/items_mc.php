<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage navigation menus');

	if ($_REQUEST['delete']) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
            $page->addError("Invalid Token");
        }
        else {
			$ditem = new \Site\Navigation\Item($_REQUEST['delete']);
			$ditem->delete();
		}
	}

	// Form Inputs
	if ($_REQUEST['parent_id']) {
		$parent = new \Site\Navigation\Item($_REQUEST['parent_id']);
		$items = $parent->children();
		$menu = $parent->menu();
	}
	elseif ($_REQUEST['menu_id']) {
		$menu = new \Site\Navigation\Menu($_REQUEST['menu_id']);
		$items = $menu->items();
	}
	elseif ($GLOBALS['_REQUEST_']->query_vars_array[0]) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$menu = new \Site\Navigation\Menu();
		$menu->get($code);
		$items = $menu->items();
	}

	$page->title("Menu Items");
	$page->addBreadcrumb("Menus", "/_navigation/menus");
	if (isset($parent)) {
		$page->addBreadcrumb($menu->title,"/_navigation/items/".$menu->code);
		if ($parent->parent_id) {
			$grandparent = new \Site\Navigation\Item($parent->parent_id);
			$page->addBreadcrumb($grandparent->title, "/_navigation/items?parent_id=".$grandparent->id);
		}
		$page->addBreadcrumb($parent->title);
	}
	else $page->addBreadcrumb($menu->title);