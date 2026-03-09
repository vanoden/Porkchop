<?php
	/** @view /_register/admin_privileges_assigned
	 * This view is used to display the privileges assigned to a specific customer. It allows administrators to see which privileges a customer has and manage them accordingly.
	 */
	$porkchop = new \Porkchop();
	$site = $porkchop->site();
	$page = $site->page();

	// See if the user specificed a customer to show
	if (!empty($_REQUEST['customer_id']) && is_numeric($_REQUEST['customer_id'])) {
		$customer = new \Register\Customer($_REQUEST['customer_id']);
		if (!$customer->exists()) {
			$page->addError("Customer not found");
			$page->notFound();
		}
	}
	elseif ($GLOBALS['_REQUEST_']->query_vars_array && count($GLOBALS['_REQUEST_']->query_vars_array) > 0 && !empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$customer = new \Register\Customer();
		if (!$customer->get($GLOBALS['_REQUEST_']->query_vars_array[0])) {
			$page->addError("Customer not found");
			$page->notFound();
		}
	}
	else {
		$customer = $GLOBALS['_SESSION_']->customer();
	}

	if (! $GLOBALS['_SESSION_']->customer()->can('manage customers',\Register\PrivilegeLevel::ADMINISTRATOR)) {
		if (! $GLOBALS['_SESSION_']->customer()->can('manage customers',\Register\PrivilegeLevel::ORGANIZATION_MANAGER)) {
			$page->permissionDenied();
		}
		else {
			// Organization Managers can only view privileges for customers in their organization
			if ($customer->organization_id != $GLOBALS['_SESSION_']->customer()->organization_id) {
				$page->permissionDenied();
			}
		}
	}
	
	$roleList = new \Register\RoleList();
	$roles = $roleList->find();

	$privileges = [];
	foreach ($roles as $role) {
		#$privilege["role"] = $role;
		foreach (\Register\PrivilegeLevel::LEVEL_NAMES as $level => $levelName) {
			#$privilege["level"] = $levelName;
			foreach ($role->privileges() as $privilege) {
				if ($customer->has_privilege($privilege->name,$level)) {
					array_push($privileges,["role" => $role->name, "level" => $levelName, "privilege" => $privilege->name]);
				}
				else {
					//echo "Customer does NOT have level: " . $levelName . "<br>\n";
				}
			}
		}
	}
	$customer_id = $customer->id;

	$page->title("Privilege Assignment Report");