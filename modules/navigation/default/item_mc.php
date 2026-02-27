<?php
	$page = new \Site\Page();
	$page->requirePrivilege("manage navigation menus");
	$can_proceed = true;

	// Create navigation item object for validation
	$navItem = new \Site\Navigation\Item();

	// Validate item identification
	if (!empty($_REQUEST['id'])) {
		if (!$navItem->validInteger($_REQUEST['id'])) {
			$page->addError("Invalid item ID format");
			$can_proceed = false;
		}
		else {
			$item = new \Site\Navigation\Item($_REQUEST['id']);
		}
	}
	elseif (preg_match('/^[0-9]+$/',$GLOBALS['_REQUEST_']->query_vars_array[0],$matches)) {
		$_REQUEST['id'] = $matches[1];
		if (!empty($_REQUEST['id']) && !$navItem->validInteger($_REQUEST['id'])) {
			$page->addError("Invalid item ID format");
			$can_proceed = false;
		}
		else {
			$item = new \Site\Navigation\Item($_REQUEST['id']);
		}
	}

	// Validate menu
	if ($can_proceed) {
		if (!empty($_REQUEST['menu_id'])) {
			if (!$navItem->validInteger($_REQUEST['menu_id'])) {
				$page->addError("Invalid menu ID format");
				$can_proceed = false;
			} else {
				$menu = new \Site\Navigation\Menu($_REQUEST['menu_id']);
				if (!$menu->exists()) {
					$page->addError("Menu not found");
					$can_proceed = false;
				}
			}
		}
	}

	// Validate parent item if provided
	if ($can_proceed && !empty($_REQUEST['parent_id'])) {
		if (isset($_REQUEST['parent_id']) && (!$navItem->validInteger($_REQUEST['parent_id']) || $_REQUEST['parent_id'] === 'undefined')) {
			// If parent_id is invalid or 'undefined', treat it as null
			$_REQUEST['parent_id'] = null;
		} else {
			$parent = new \Site\Navigation\Item($_REQUEST['parent_id']);
		}
	}

	// Handle form submission
	if ($can_proceed && isset($_REQUEST['btn_submit'])) {
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'] ?? '')) {
			$page->addError("Invalid Request");
			$can_proceed = false;
		}
		
		if ($can_proceed) {
			if (!isset($item)) {
				$item = new \Site\Navigation\Item();
			}
			
			if (empty($_REQUEST['title']) || !$navItem->validTitle($_REQUEST['title'])) {
				$page->addError("Invalid title");
				$can_proceed = false;
			}
			
			if ($can_proceed) {
				$parameters = array(
					"title" => noXSS($_REQUEST['title']),
					"target" => noXSS($_REQUEST['target'] ?? ''),
					"alt" => noXSS($_REQUEST['alt'] ?? ''),
					"required_role_id" => $_REQUEST['required_role_id'] ?? null,
					"view_order" => filter_var($_REQUEST['view_order'] ?? 0, FILTER_VALIDATE_INT),
					"description" => noXSS($_REQUEST['description'] ?? ''),
					"authentication_required" => isset($_REQUEST['authentication_required']) ? 1 : 0,
					"thumbnail_url" => noXSS($_REQUEST['thumbnail_url'] ?? ''),
					"required_product_id" => $_REQUEST['required_product_id'] ?? null,
				);
				
				if ($item->id > 0) {
					app_log("Updating menu " . $item->id);
					$item->update($parameters);
					if ($item->error()) {
						$page->addError($item->error());
					} else {
						$page->appendSuccess("Item Updated");
					}
				} elseif (strlen($_REQUEST['title']) > 0) {
					app_log("Adding new menu '" . $_REQUEST['title'] . "'");
					$parameters['menu_id'] = $menu->id;
					$parameters['parent_id'] = $parent->id ?? null;
					
					$item = new \Site\Navigation\Item();
					$item->add($parameters);
					if ($item->error()) {
						$page->addError($item->error());
					} else {
						$page->appendSuccess("Item Added");
					}
				}
			}
		}
	} elseif ($can_proceed && isset($_REQUEST['btn_delete'])) {
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'] ?? '')) {
			$page->addError("Invalid Request");
			$can_proceed = false;
		}
		
		if ($can_proceed) {
			$item->delete();
			if ($item->error()) {
				$page->addError($item->error());
			} else {
				$page->appendSuccess("Item Deleted");
			}
		}
	}

	// Load roles for dropdown
	$roleList = new \Register\RoleList();
	$roles = $roleList->find();

	// Load products for dropdown
	$productList = new \Product\ItemList();
	$products = $productList->find(array("active" => 1, "order_by" => "code", "type" => "service"));

	$page->title("Menu Item Details");
	$page->setAdminMenuSection("Site");  // Keep Navigation section open
	$page->addBreadcrumb("Menus", "/_navigation/menus");
	if (isset($parent)) {
		$page->addBreadcrumb($menu->title, "/_navigation/items/" . $menu->code);
		if ($parent->parent_id) {
			$grandparent = new \Site\Navigation\Item($parent->parent_id);
			$page->addBreadcrumb($grandparent->title, "/_navigation/items?parent_id=" . $grandparent->id);
		}
		$page->addBreadcrumb($parent->title, "/_navigation/items?parent_id=" . $parent->id);
	}
	if (isset($item) && $item->id) {
		$page->addBreadcrumb($item->title);
	} else {
		$page->addBreadcrumb('New Menu Item');
	}
