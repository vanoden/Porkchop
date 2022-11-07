<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage navigation menus');

	if ($_REQUEST['id']) {
		$menu = new \Navigation\Menu($_REQUEST['id']);
	} elseif ($GLOBALS['_REQUEST_']->query_vars_array[0]) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$menu = new \Navigation\Menu();
		$menu->get($code);
	}

	if (isset($_REQUEST['btn_submit'])) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
            $page->addError("Invalid Token");
        }
        elseif(!$menu->validCode($_REQUEST['code'])) {
            $page->addError("Invalid code");
        }
        else {
            $_REQUEST['menu_title'] = noXSS($_REQUEST['menu_title']);
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
                    "title"			=> noXSS($_REQUEST['title'][$id]),
                    "target"		=> noXSS($_REQUEST['target'][$id]),
                    "alt"			=> noXSS($_REQUEST['alt'][$id]),
                    "view_order"	=> filter_var($_REQUEST['view_order'][$id],FILTER_VALIDATE_INT),
                    "description"	=> noXSS($_REQUEST['description'][$id])
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
	}
	
	if ($_REQUEST['delete']) {
		$item = new \Navigation\Item($_REQUEST['delete']);
		$item->delete();
	}

	if (isset($menu)) $items = $menu->items();
