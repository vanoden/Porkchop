<?php
    $page = new \Site\Page();
    $page->requirePrivilege('manage privileges');

    if (! empty($_REQUEST['newPrivilege'])) {
        $privilege = new \Register\Privilege();
        if ($privilege->add(array('name' => $_REQUEST['newPrivilege']))) {
            $page->success = "Privilege Added";
        }
        else {
            $page->addError("Error adding privilege: ".$privilege->error());
        }
    }
	elseif ($_REQUEST['btn_update'] == "Update") {
        $privilege = new \Register\Privilege($_REQUEST['privilege_id']);
        if ($privilege->id) {
			if (!$privilege->update(array('name' => $_REQUEST['name']['privilege_id'], 'module' => $_REQUEST['module'][$privilege->id]))) {
				$page->addError("Error updating privilege: ".$privilege->error());
			}
		}
	}
    elseif ($_REQUEST['btn_delete'] == "Delete") {
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
    $privilegeList = new \Register\PrivilegeList();
    $privileges = $privilegeList->find(array('_sort' => 'module'));
?>
