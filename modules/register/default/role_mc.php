<?php
	$page = new \Site\Page();
	$page->requireRole('register manager');

	$role_name = $_REQUEST['name'];
	if (empty($role_name)) $role_name = $GLOBALS['_REQUEST_']->query_vars_array[0];

	$role = new \Register\Role();
	if ($role->get($role_name)) {
		if (isset($_REQUEST['btn_submit'])) {
			if ($role->update(
				array(
					'description'	=> $_REQUEST['description']
				)
			)) {
				$page->success = "Role Updated";
			}
			else {
				$page->addError("Role update failed: ".$role->error);
			}
		}
		$privileges = $role->privileges();
	} else {
		$page->addError("Role not found");
	}
