<?php
	$page = new \Site\Page();
	$page->requirePrivilege("manage navigation menus");

	if (!empty($_REQUEST['id'])) {
		$item = new \Navigation\Item($_REQUEST['id']);
	}
	elseif ($GLOBALS['_REQUEST_']->query_vars_array[0]) {
		$_REQUEST['id'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$item = new \Navigation\Item($_REQUEST['id']);
	}

	$menu = new \Navigation\Menu($_REQUEST['menu_id']);
	if (! $menu->exists()) {
		$page->addError("Menu not found");
	}
	$parent = new \Navigation\Item($_REQUEST['parent_id']);

	if (isset($_REQUEST['btn_submit'])) {
		if (!isset($item)) $item = new \Navigation\Item();
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
            $page->addError("Invalid Token");
        }
        elseif(!$item->validCode($_REQUEST['title'])) {
            $page->addError("Invalid title");
        }
        else {
            $parameters = array(
                "title"			=> noXSS($_REQUEST['title']),
                "target"		=> noXSS($_REQUEST['target']),
                "alt"			=> noXSS($_REQUEST['alt']),
				"required_role_id"	=> $_REQUEST['required_role_id'],
                "view_order"	=> filter_var($_REQUEST['view_order'],FILTER_VALIDATE_INT),
                "description"	=> noXSS($_REQUEST['description'])
            );
            if ($item->id > 0) {
                app_log("Updating menu $id");
                $item->update($parameters);
				if ($item->error()) $page->addError($item->error());
				else $page->success = "Item Updated";
            }
            elseif(strlen($_REQUEST['title']) > 0) {
                app_log("Adding new menu '$title'");
				$parameters['menu_id'] = $menu->id;
				$parameters['parent_id'] = $parent->id;

                $item = new \Navigation\Item();
                $item->add($parameters);
				if ($item->error()) $page->addError($item->error());
				else $page->success = "Item Added";
            }
            if ($item->error) {
                $page->addError($item->error);
            }
        }
    }
	elseif (isset($_REQUEST['btn_delete'])) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
            $page->addError("Invalid Token");
        }
        else {
			$item->delete();
		}
	}

	$roleList = new \Register\RoleList();
	$roles = $roleList->find();

	$page->addBreadcrumb("Menus", "/_navigation/menus");
	if (isset($parent)) {
		$page->addBreadcrumb($menu->title,"/_navigation/items/".$menu->code);
		if ($parent->parent_id) {
			$grandparent = new \Navigation\Item($parent->parent_id);
			$page->addBreadcrumb($grandparent->title, "/_navigation/items?parent_id=".$grandparent->id);
		}
		$page->addBreadcrumb($parent->title,"/_navigation/items?parent_id=".$parent->id);
	}
	if ($item->id) $page->addBreadcrumb($item->title);
	else $page->addBreadcrumb('New Menu Item');
