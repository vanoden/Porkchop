<?php
    $page = new \Site\Page();
    $page->requirePrivilege('manage privileges');

    if ($_REQUEST['newPrivilege']) {
        $privilege = new \Register\Privilege();
        if ($privilege->add(array('name' => $_REQUEST['newPrivilege']))) {
            $page->success = "Privilege Added";
        }
        else {
            $page->addError("Error adding privilege: ".$privilege->error());
        }
    }
    if ($_REQUEST['delete_id']) {
        $privilege = new \Register\Privilege($_REQUEST['delete_id']);
        if ($privilege->id) {
            if ($privilege->delete()) {
                $page->success = "Privilege '".$privilege->name."' deleted";
            }
            else {
                $page->addError("Failed to delete privilege: ".$privilege->error());
            }
        }
    }
    $privilegeList = new \Register\PrivilegeList();
    $privileges = $privilegeList->find();
?>