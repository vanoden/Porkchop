<?php
	$page = new \Site\Page();
	$page->requireRole('content operator');

	if ($_REQUEST['id']) {
		$menu = new \Navigation\Menu($_REQUEST['id']);
	} elseif ($GLOBALS['_REQUEST_']->query_vars_array[0]) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$menu = new \Navigation\Menu();
		$menu->get($code);
	}

	if (isset($_REQUEST['btn_submit'])) {
		$parameters = array(
			"code"	=> $_REQUEST['code'],
			"title"	=> $_REQUEST['menu_title']
		);
		$menu->update($parameters);
		if ($menu->error) {
			$page->addError($menu->error);
		}
		foreach($_REQUEST['title'] as $id => $title) {
			$parameters = array(
				"menu_id"		=> $menu->id,
				"title"			=> $_REQUEST['title'][$id],
				"target"		=> $_REQUEST['target'][$id],
				"alt"			=> $_REQUEST['alt'][$id],
				"view_order"	=> $_REQUEST['view_order'][$id],
				"description"	=> $_REQUEST['description'][$id]
			);
			if ($id > 0) {
				app_log("Updating menu $id");
				$item = new \Navigation\Item($id);
				$item->update($parameters);
			}
			elseif(strlen($_REQUEST['title'][$id]) > 0) {
				app_log("Adding new menu '$title'");
				$item = new \Navigation\Item();
				$item->add($parameters);
			}

			if ($item->error) {
				$page->addError($item->error);
			}
		}
	}
	
	if ($_REQUEST['delete']) {
		$item = new \Navigation\Item($_REQUEST['delete']);
		$item->delete();
	}

	if (isset($menu)) $items = $menu->items();
