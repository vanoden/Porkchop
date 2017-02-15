<?PHP
	###################################################
	### organization_mc.php							###
	### This program collects organization info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################
	if (! $GLOBALS['_SESSION_']->authenticated()) {
		header("location: /_register/login?target=_register:organization");
		exit;
	}
	# Security - Only Register Module Operators or Managers can see other customers
	if ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
		if (preg_match('/^\d+$/',$_REQUEST['organization_id'])) {
			$organization = new \Register\Organization($_REQUEST['organization_id']);
			if ($organization->error) {
				$GLOBALS['_page']->error = "Unable to load organization: ".$organization->error;
			}
		}
		elseif (preg_match('/^[\w\-\.\_]+$/',$GLOBALS['_REQUEST_']->query_vars_array[0])) {
			$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
			$organization = new \Register\Organization();
			$organization->get($code);
			if (! $organization->id) $GLOBALS['_page']->error = "Customer not found";
		}
		else $organization = new \Register\Organization();
	}
	else $organization = $GLOBALS['_SESSION_']->customer->organization;

	if ($_REQUEST['method']) {
		$GLOBALS['_page']->success = $_REQUEST['method'];
		if (! $_REQUEST['name']) {
			$GLOBALS['_page']->error = "Name required";
		}
		else {
			$parameters = array(
				"name"	=> $_REQUEST['name'],
				"code"	=> $_REQUEST['code'],
				"status"	=> $_REQUEST['status']
			);
			if (is_object($organization)) {
				# Update Existing Organization
				$organization->update($parameters);

				if ($organization->error) {
					$GLOBALS['_page']->error = "Error updating organization";
				}
				else {
					$GLOBALS['_page']->success = "Organization Updated Successfully";
				}
				if ($_REQUEST['new_login']) {
					$present_customer = new \Register\Customer();

					# Make Sure Login is unique
					$present_customer->get($_REQUEST['new_login']);
					if ($present_customer->id) {
						$GLOBALS['_page']->error = "Login already exists";
					}
					else {
						$customer = new \Register\Customer();
						$customer->add(
							array(
								"login"			=> $_REQUEST['new_login'],
								"first_name"	=> $_REQUEST['new_first_name'],
								"last_name"		=> $_REQUEST['new_last_name'],
								"organization_id"	=> $organization->id,
								"password"			=> uniqid()
							)
						);
						if ($customer->error) {
							$GLOBALS['_page']->error = "Error adding customer to organization: ".$customer->error;
						}
						else {
							$GLOBALS['_page']->success = "Customer added to organization";
						}
					}
				}
			}
			else {
				if (! $parameters['code']) $parameters['code'] = uniqid();

				# See if code used
				$present_org = new \Register\Organization();
				$present_org->get($parameters['code']);
				if ($present_org->id) {
					$GLOBALS['_page']->error = "Organization code already used";
				}
				else {
					# Add Existing Organization
					$organization = new \Register\Organization();
					$organization->add($parameters);
					if ($organization->error) {
						$GLOBALS['_page']->error = "Error updating organization";
					}
					else {
						$GLOBALS['_page']->success = "Organization ".$organization->id." Created Successfully";
					}
				}
			}
		}
	}
	if ($organization->id) {
		$members = $organization->members();
		if ($organization->error) {
			$GLOBALS['_page']->error = "Error finding members: ".$organization->error;
			app_log("Error finding members: ".$organization->error,'error',__FILE__,__LINE__);
		}
	}
?>
