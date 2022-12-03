<?php
    $page = new \Site\Page();
    $page->requirePrivilege('manage privileges');

    $privilege = new \Register\Privilege();
    if (! empty($_REQUEST['newPrivilege'])) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
            $page->addError("Invalid Request");
        }
		elseif (! $privilege->validCode($_REQUEST['newPrivilege'])) {
			$page->addError("Invalid Privilege Name");
		}
		else {
            if ($privilege->add(array('name' => $_REQUEST['newPrivilege']))) {
                $page->success = "Privilege Added";
            } else {
                $page->addError("Error adding privilege: ".$privilege->error());
            }
        }
    }
	elseif ($_REQUEST['btn_update'] == "Update") {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
        	$page->addError("Invalid Request");
        }
		else {
            $privilege = new \Register\Privilege($_REQUEST['privilege_id']);
            if ($privilege->id) {
				$module = new \Site\Module();
				if (!$privilege->validCode($_REQUEST['name'][$privilege->id])) $page->addError("Invalid name '".$_REQUEST['name'][$privilege->id]."'");
				elseif (!$module->validName($_REQUEST['module'][$privilege->id])) $page->addError("Invalid module");
				else {
					$params = array(
						'name' => $_REQUEST['name'][$privilege->id],
						'module' => $_REQUEST['module'][$privilege->id]
					);
			        if (!$privilege->update($params)) $page->addError("Error updating privilege: ".$privilege->error());
				}
	        }
        }
	}
	elseif ($_REQUEST['btn_delete'] == "Delete") {
	    if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
        	$page->addError("Invalid Request");
	    }
		else {
            $privilege = new \Register\Privilege($_REQUEST['privilege_id']);
            if ($privilege->id) {
                if ($privilege->delete()) {
                    $page->success = "Privilege '".$privilege->name."' deleted";
                }
				else {
                    $page->addError("Failed to delete privilege: ".$privilege->error());
                }
            }
			else {
			    $page->addError("Privilege not found");
		    }
	    }
    }
    $privilegeList = new \Register\PrivilegeList();
    $privileges = $privilegeList->find(array('_sort' => 'module'));
	if ($privilegeList->error()) $page->addError($privilegeList->error());
