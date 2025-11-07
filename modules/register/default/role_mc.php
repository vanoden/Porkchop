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
            // Check if user can modify role privileges
            if (!$GLOBALS['_SESSION_']->customer->canModifyRolePrivileges($role)) {
                $page->addError("You do not have permission to modify role privileges");
            } else {
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
	}

	// Check permissions before displaying/modifying role privileges
	if ($role->id && !$GLOBALS['_SESSION_']->customer->canModifyRolePrivileges($role)) {
		$page->addError("You do not have permission to modify role privileges");
	}

	/** Add/Update/Remove Privileges from Role based on Form Input **/
	$privileges = array();
	if ($role->id) {
		$privilegeList = new \Register\PrivilegeList();
	    $privileges = $privilegeList->find(array('_sort' => 'module'));
		if (isset($_REQUEST['btn_submit'])) {
		    if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
                $page->addError("Invalid Request");
            } else {
                // Check if user can modify role privileges
                if (!$GLOBALS['_SESSION_']->customer->canModifyRolePrivileges($role)) {
                    $page->addError("You do not have permission to modify role privileges");
                }
				else {
		            foreach ($privileges as $privilege) {
						$new_level = $role->getPrivilegeLevel($privilege->id);
						$old_level = $new_level;
						$levels = array(0,2,3,5,7);
						foreach ($levels as $level) {
							// Remove if unselected
							if ($role->has_privilege($privilege->id,$level) && ! $_REQUEST['privilege_level'][$privilege->id][$level]) {
								//$current_level = setMatrix($current_level, $level, false);
								$page->appendSuccess("Removed privilege &quot;".$privilege->name."&quot; level ".$level." from role &quot;".$role->name."&quot;.");
								$new_level = setMatrix($new_level, $level, false);
							}
							// Add if selected
							elseif (! $role->has_privilege($privilege->id,$level) && $_REQUEST['privilege_level'][$privilege->id][$level]) {
								//$current_level = setMatrix($current_level, $level, true);
								$page->appendSuccess("Added privilege &quot;".$privilege->name."&quot; level ".$level." to role &quot;".$role->name."&quot;.");
								$new_level = setMatrix($new_level, $level, true);
							}
	                    }
						// Update privilege level if changed
						if ($new_level != $old_level) {
							$role->setPrivilegeLevel($privilege->id, $new_level);
						}
					}
				}
			}
		}

		// See if all privileges checked
	    $privileges = $privilegeList->find(array('_sort' => 'module'));
		foreach ($privileges as $privilege) {
			if (! $role->has_privilege($privilege->id)) {
				$allChecked = false;
				break;
			}
		}
	}

	// Page Header
	$page->title = "Role Details";
	$page->setAdminMenuSection("Customer");  // Keep Customer section open
	$page->addBreadcrumb("Customer");
	$page->addBreadcrumb("Roles", "/_register/roles");
	if (!empty($role->id)) {
		$page->addBreadcrumb($role->name);
	}
	else {
		$page->addBreadcrumb("New Role");
	}
