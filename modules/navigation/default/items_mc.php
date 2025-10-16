<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage navigation menus');
	$can_proceed = true;

	// Create navigation item object for validation
	$navItem = new \Site\Navigation\Item();

	if (!empty($_REQUEST['delete'])) {
        if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'] ?? '')) {
            $page->addError("Invalid Request");
            $can_proceed = false;
        }
        
        if ($can_proceed) {
            if (!$navItem->validInteger($_REQUEST['delete'])) {
                $page->addError("Invalid item ID format");
                $can_proceed = false;
            } else {
                $ditem = new \Site\Navigation\Item($_REQUEST['delete']);
                $ditem->delete();
                if ($ditem->error()) {
                    $page->addError($ditem->error());
                } else {
                    $page->appendSuccess("Item deleted successfully");
                }
            }
        }
	}

	// Form Inputs
	if ($can_proceed) {
		if (!empty($_REQUEST['parent_id'])) {
			if (!$navItem->validInteger($_REQUEST['parent_id'])) {
				$page->addError("Invalid parent ID format");
				$can_proceed = false;
			} else {
				$parent = new \Site\Navigation\Item($_REQUEST['parent_id']);
				if (!$parent->exists()) {
					$page->addError("Parent item not found");
					$can_proceed = false;
				} else {
					$items = $parent->children();
					$menu = $parent->menu();
				}
			}
		} elseif (!empty($_REQUEST['menu_id'])) {
			if (!$navItem->validInteger($_REQUEST['menu_id'])) {
				$page->addError("Invalid menu ID format");
				$can_proceed = false;
			} else {
				$menu = new \Site\Navigation\Menu($_REQUEST['menu_id']);
				if (!$menu->exists()) {
					$page->addError("Menu not found");
					$can_proceed = false;
				} else {
					$items = $menu->items();
				}
			}
		} elseif ($GLOBALS['_REQUEST_']->query_vars_array[0]) {
			$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
			if (!$navItem->validCode($code)) {
				$page->addError("Invalid menu code format");
				$can_proceed = false;
			} else {
				$menu = new \Site\Navigation\Menu();
				$menu->get($code);
				if (!$menu->exists()) {
					$page->addError("Menu not found");
					$can_proceed = false;
				} else {
					$items = $menu->items();
				}
			}
		}
	}

	$page->title("Menu Items");
	$page->setAdminMenuSection("Site");  // Keep Site section open
	$page->addBreadcrumb("Menus", "/_navigation/menus");
	if (isset($parent)) {
		$page->addBreadcrumb($menu->title,"/_navigation/items/".$menu->code);
		if ($parent->parent_id) {
			$grandparent = new \Site\Navigation\Item($parent->parent_id);
			$page->addBreadcrumb($grandparent->title, "/_navigation/items?parent_id=".$grandparent->id);
		}
		$page->addBreadcrumb($parent->title);
	}
	else $page->addBreadcrumb($menu->title ?? 'Menu Items');

// Ensure variables are defined before including template
if (!isset($items)) {
	$items = [];
}
if (!isset($menu)) {
	$page->addError("No menu specified");
	$page->render();
	exit;
}
