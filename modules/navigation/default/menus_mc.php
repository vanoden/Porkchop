<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage navigation menus');
	$can_proceed = true;

	// Create menu object for validation
	$menu = new \Site\Navigation\Menu();

	// Handle form submission
	if (!empty($_REQUEST['btn_submit'])) {
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'] ?? '')) {
			$page->addError("Invalid Request");
			$can_proceed = false;
		}
		
		if ($can_proceed) {
			// Validate required fields
			if (empty($_REQUEST['code']) || !$menu->validCode($_REQUEST['code'])) {
				$page->addError("Invalid menu code");
				$can_proceed = false;
			}
			
			if (empty($_REQUEST['title']) || !$menu->validText($_REQUEST['title'])) {
				$page->addError("Invalid menu title");
				$can_proceed = false;
			}
			
			if ($can_proceed) {
				if (empty($_REQUEST['id'])) {
					$menu = new \Site\Navigation\Menu();
					$menu->add(array(
						'code' => $_REQUEST['code'],
						'title' => $_REQUEST['title']
					));
					if ($menu->error()) {
						$page->addError($menu->error());
					} else {
						$page->appendSuccess("Menu added successfully");
					}
				} else {
					if (!$menu->validInteger($_REQUEST['id'])) {
						$page->addError("Invalid menu ID format");
						$can_proceed = false;
					} else {
						$menu = new \Site\Navigation\Menu($_REQUEST['id']);
						if (!$menu->exists()) {
							$page->addError("Menu not found");
							$can_proceed = false;
						} else {
							$menu->update(array(
								'code' => $_REQUEST['code'],
								'title' => $_REQUEST['title']
							));
							if ($menu->error()) {
								$page->addError($menu->error());
							} else {
								$page->appendSuccess("Menu updated successfully");
							}
						}
					}
				}
			}
		}
	}

	// Load menu list
	$menuList = new \Site\Navigation\MenuList();
	$menus = $menuList->find();
	if ($menuList->error()) {
		$page->addError($menuList->error());
	}