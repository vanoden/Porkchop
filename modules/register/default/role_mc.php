<?php
	$page = new \Site\Page();
	$page->requireRole('register manager');

    if ($_REQUEST['id']) $role = new \Register\Role($_REQUEST['id']);
    else {
    	$role_name = isset($_REQUEST['name']) ? $_REQUEST['name']: '';
	    if (empty($role_name)) $role_name = $GLOBALS['_REQUEST_']->query_vars_array[0];

    	$role = new \Register\Role();
    	$role->get($role_name);
    }
    if ($role->id) {
		if (isset($_REQUEST['btn_submit'])) {
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
	} else {
		$page->addError("Role not found");
	}

	$privilegeList = new \Register\PrivilegeList();
    $privileges = $privilegeList->find();
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