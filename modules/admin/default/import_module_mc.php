<?php
// No Longer Used
exit;
	require_once(MODULES."/admin/_classes/admin.php");
	$name = $GLOBALS['_page']->query_vars_array[0];
	$_module = new PorkchopModule();
	list($module) = $_module->find(array("name" => $name));
	if ($module) {
		print"Importing '$name' Module<br>\n";
		# Add Roles
		foreach ($module->role as $role) {
			print "Adding role '".$role["title"]."'<br>\n";
			$role = new \Register\Role();
			$role->add(
				array(
					"title"	=> $role["title"],
					"description"	=> $role["description"]
				)
			);
			if ($role->error()) {
				print "Failed to add role: ".$role->error();
				exit;
			}
		}
	} else {
		print "Module '$name' not installed";
	}
