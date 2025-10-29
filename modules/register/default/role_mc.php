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
                } else {
		            foreach ($privileges as $privilege) {
		                // Get selected privilege levels from checkboxes
		                // Check both $_POST and $_REQUEST in case of form submission issues
		                $selected_levels = array();
		                if (isset($_POST['privilege_level'][$privilege->id])) {
		                    $selected_levels = $_POST['privilege_level'][$privilege->id];
		                } elseif (isset($_REQUEST['privilege_level'][$privilege->id])) {
		                    $selected_levels = $_REQUEST['privilege_level'][$privilege->id];
		                }
		                
		                // Ensure it's an array
		                if (!is_array($selected_levels)) {
		                    $selected_levels = array();
		                }
		                
		                $current_level = $role->getPrivilegeLevel($privilege->id);
		                
		                // Debug: Show what was submitted for each privilege (only if levels were selected)
		                if (!empty($selected_levels)) {
		                    $page->appendSuccess("DEBUG: Privilege '{$privilege->name}' (ID: {$privilege->id}): Selected levels: " . json_encode($selected_levels) . ", Current level: " . ($current_level ?? 'null'));
		                }
		                
		                // If no levels are selected, remove the privilege
		                if (empty($selected_levels)) {
		                    if ($current_level !== null && $role->dropPrivilege($privilege->id)) {
		                        $page->appendSuccess("Removed privilege '".$privilege->name."'");
		                    }
		                } else {
		                    // Calculate the combined privilege level using addition
		                    // This allows multiple privilege levels to be combined (e.g., 22 = 15 + 7)
		                    $new_level = 0;
		                    foreach ($selected_levels as $level) {
		                        $new_level += (int)$level;
		                    }
		                    
		                    // Debug: Show calculated level
		                    if (!empty($selected_levels)) {
		                        $page->appendSuccess("DEBUG: Privilege '{$privilege->name}': Calculated new level: $new_level (from: " . implode(' + ', $selected_levels) . ")");
		                    }
		                    
		                    if ($current_level === null) {
		                        // Add new privilege
		                        if ($role->addPrivilege($privilege->id, $new_level)) {
		                            $level_names = array();
		                            foreach ($selected_levels as $level) {
		                                $level_names[] = \Register\PrivilegeLevel::privilegeName((int)$level);
		                            }
		                            $page->appendSuccess("Added privilege '".$privilege->name."' with levels: ".implode(', ', $level_names));
		                        } else {
		                            $page->addError("Failed to add privilege '{$privilege->name}': " . $role->error());
		                        }
		                    } elseif ($current_level != $new_level) {
		                        // Update existing privilege level
		                        if ($role->addPrivilege($privilege->id, $new_level)) {
		                            $old_level_name = \Register\PrivilegeLevel::privilegeName($current_level);
		                            $new_level_names = array();
		                            foreach ($selected_levels as $level) {
		                                $new_level_names[] = \Register\PrivilegeLevel::privilegeName((int)$level);
		                            }
		                            $page->appendSuccess("Updated privilege '".$privilege->name."' level from '".$old_level_name."' to '".implode(', ', $new_level_names)."'");
		                        } else {
		                            $page->addError("Failed to update privilege '{$privilege->name}': " . $role->error());
		                        }
		                    }
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
	$page->setAdminMenuSection("Customer");  // Keep Customer section open
	$page->addBreadcrumb("Customer");
	$page->addBreadcrumb("Roles", "/_register/roles");
	if (!empty($role->id)) {
		$page->addBreadcrumb($role->name);
	}
	else {
		$page->addBreadcrumb("New Role");
	}
