<?php
	$page = new \Site\Page();
	$page->requireRole('register manager');

	# Identify Specified Role if possible
    if ($_REQUEST['id']) {
		$role = new \Register\Role($_REQUEST['id']);
		if (! $role->id) $page->addError("Role &quot;".$_REQUEST['id']."&quot; not found");
	}
	if (! $role->id && isset($_REQUEST['name']) && strlen($_REQUEST['name']) > 0) {
    	$role = new \Register\Role();
    	$role->get($_REQUEST['name']);
		if (! $role->id) {
			if (isset($_REQUEST['btn_submit'])) {
				$role->add(array(
					"name" => $_REQUEST['name'],
					"description" => $_REQUEST['description']
				));
			}
			else {
				$page->addError("Role &quot;".$_REQUEST['name']."&quot; not found");
			}
		}
	}
	if (! $role->id && isset($GLOBALS['_REQUEST_']->query_vars_array[0]) && strlen($GLOBALS['_REQUEST_']->query_vars_array[0])) {
    	$role_name = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$role = new \Register\Role();
		$role->get($role_name);
		if (! $role->id) $page->addError("Role named &quot;".$role_name."&quot; not found");
    }

    if ($role->id && isset($_REQUEST['btn_submit'])) {
		if ($role->update(
			array(
				'description'	=> $_REQUEST['description']
			)
		)) {
			$page->success = "Role Updated";
		} else {
			$page->addError("Role update failed: ".$role->error);
		}
	}

	if ($role->id) {
		$privilegeList = new \Register\PrivilegeList();
	    $privileges = $privilegeList->find(array('_sort' => 'module'));
		if (isset($_REQUEST['btn_submit'])) {
		    foreach ($privileges as $privilege) {
		        if ($_REQUEST['privilege'][$privilege->id] == 1) {
		            if (! $role->has_privilege($privilege->id) && $role->addPrivilege($privilege->id)) {
		                $page->success .= "Added privilege '".$privilege->name."'";
		            }
		        }
		        else {
		            if ($role->has_privilege($privilege->id) && $role->dropPrivilege($privilege->id)) {
		                $page->success .= "Removed privilege '".$privilege->name."'";
		            }
		        }
			}
	    }
	}
