<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage customers');

	$role = new \Register\Role();
	if (isset($_REQUEST['remove_id']) && is_numeric(intval($_REQUEST['remove_id']))) {
		$role = new \Register\Role($_REQUEST['remove_id']);
		if (! $role->id) {
			$page->addError("Role &quot;".$_REQUEST['id']."&quot; not found");
		}
		else {
			// remove any privileges associated with this role
			$privilegeList = $role->privileges();
			if (!empty($privilegeList) && is_array($privilegeList)) {
				foreach ($privilegeList as $privilege) {
					if (!$role->dropPrivilege($privilege->id)) $page->addError("Role privilege drop failed: ".$role->error());
				}
			}

			if (!$role->removeMembers()) $page->addError("Role members delete failed: ".$role->error());
			if (!$role->delete()) $page->addError("Role delete failed: ".$role->error());
			//if ($page->errorCount() == 0) $page->success = "Role removed";
		}
	}
	
	$roleList = new \Register\RoleList();
	$roles = $roleList->find();
	if ($roleList->error()) $page->addError($roleList->error());