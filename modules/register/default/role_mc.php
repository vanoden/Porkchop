<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage customers');

	// Identify Specified Role if possible
	$role = new \Register\Role();
    if (!empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
		$role = new \Register\Role($_REQUEST['id']);
		if (! $role->id) $page->addError("Role &quot;".$_REQUEST['id']."&quot; not found");
	}
	/** Add New Role **/	
	elseif (! $role->id && isset($_REQUEST['name']) && $role->validName($_REQUEST['name'])) {
    	$role = new \Register\Role();
    	$role->get($_REQUEST['name']);
		if (! $role->id) {
			if (isset($_REQUEST['btn_submit'])) {
                if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
                    $page->addError("Invalid Request");
                }
				else {
                    $role->add(array(
	                    "name" => $_REQUEST['name'],
	                    "description" => noXSS(trim($_REQUEST['description'])),
                        "time_based_password" => isset($_REQUEST['time_based_password']) ? 1 : 0
                    ));
                }
			}
			else {
				$page->addError("Role not found");
			}
		}
	}
	/** Role name provided in URL but Role not found **/
	elseif (! $role->id && isset($GLOBALS['_REQUEST_']->query_vars_array[0]) && strlen($GLOBALS['_REQUEST_']->query_vars_array[0])) {
    	$role_name = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$role = new \Register\Role();
		$role->get($role_name);
		if (! $role->id) $page->addError("Role not found");
    }

	/** Update Existing Role **/
    if ($role->id && isset($_REQUEST['btn_submit'])) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
            $page->addError("Invalid Request");
        }
		else {
            $updateParams = array(
                'description' => noXSS(trim($_REQUEST['description'])),
                'time_based_password' => isset($_REQUEST['time_based_password']) ? 1 : 0
            );
            
		    if ($role->update($updateParams)) {
			    $page->appendSuccess("Role Updated");
		    }
			else {
			    $page->addError("Role update failed: ".$role->error());
		    }
        }
	}

	/** Add/Remove Privileges from Role based on Form Input **/
	$allChecked = false;
	$privileges = array();
	if ($role->id) {
		$allChecked = true;
		$privilegeList = new \Register\PrivilegeList();
	    $privileges = $privilegeList->find(array('_sort' => 'module'));
		if (isset($_REQUEST['btn_submit'])) {
		    if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
                $page->addError("Invalid Request");
            } else {
            
		        foreach ($privileges as $privilege) {
		            if (isset($_REQUEST['privilege'][$privilege->id]) && $_REQUEST['privilege'][$privilege->id] == 1) {
		                if (! $role->has_privilege($privilege->id) && $role->addPrivilege($privilege->id)) {
		                    $page->appendSuccess("Added privilege '".$privilege->name."'");
		                }
		            }
		            else {
		                if ($role->has_privilege($privilege->id) && $role->dropPrivilege($privilege->id)) {
		                    $page->appendSuccess("Removed privilege '".$privilege->name."'");
		                }
		            }
			    }
            }
	    }

		// See if all privileges checked
		foreach ($privileges as $privilege) {
			if (! $role->has_privilege($privilege->id)) {
				$allChecked = false;
				break;
			}
		}
	}

	// Page Header
	$page->title = "Role Details";
	$page->addBreadcrumb("Roles", "/_register/roles");
	if (!empty($role->id)) {
		$page->addBreadcrumb($role->name);
	}
	else {
		$page->addBreadcrumb("New Role");
	}
