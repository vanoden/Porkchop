<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage customers');

	// Identify Specified Role if possible
	$role = new \Register\Role();
    if (!empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
		$role = new \Register\Role($_REQUEST['id']);
		if (! $role->id) $page->addError("Role &quot;".$_REQUEST['id']."&quot; not found");
	}
	
	if (! $role->id && isset($_REQUEST['name']) && $role->validName($_REQUEST['name'])) {
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
	                    "description" => noXSS(trim($_REQUEST['description']))
                    ));
                }
			}
			else {
				$page->addError("Role not found");
			}
		}
	}

	if (! $role->id && isset($GLOBALS['_REQUEST_']->query_vars_array[0]) && strlen($GLOBALS['_REQUEST_']->query_vars_array[0])) {
    	$role_name = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$role = new \Register\Role();
		$role->get($role_name);
		if (! $role->id) $page->addError("Role not found");
    }

    if ($role->id && isset($_REQUEST['btn_submit'])) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
            $page->addError("Invalid Request");
        } else {
		    if ($role->update( array( 'description'	=> noXSS(trim($_REQUEST['description'] ))))) {
			    $page->appendSuccess("Role Updated");
		    }
			else {
			    $page->addError("Role update failed: ".$role->error());
		    }
        }
	}

	$privileges = array();
	if ($role->id) {
		$privilegeList = new \Register\PrivilegeList();
	    $privileges = $privilegeList->find(array('_sort' => 'module'));
		if (isset($_REQUEST['btn_submit'])) {
		    if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
                $page->addError("Invalid Request");
            } else {
            
		        foreach ($privileges as $privilege) {
		            if ($_REQUEST['privilege'][$privilege->id] == 1) {
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
	}

	$page->title = "Role Editor";
	$page->addBreadcrumb("Roles", "/_register/roles");
	if (isset($role->id)) {
		$page->addBreadcrumb($role->name);
	}