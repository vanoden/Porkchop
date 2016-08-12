<?PHP
	###################################################
	### register_mc.php								###
	### This program collects registration info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################

	$_organization = new RegisterOrganization();
	$organization_id = 0;

	# Initialize Organization Object
	$_organization = new RegisterOrganization();

	# Security - Only Register Module Operators or Managers can see other customers
	if (role('register manager'))
	{
		if (preg_match('/^\d+$/',$_REQUEST['organization_id'])) $organization_id = $_REQUEST['organization_id'];
		elseif (preg_match('/^[\w\-\.\_]+$/',$GLOBALS['_REQUEST_']->query_vars_array[0]))
		{
			$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
			$organization = $_organization->get($code);
			if ($organization->id)
				$organization_id = $organization->id;
			else
				$GLOBALS['_page']->error = "Customer not found";
		}
	}
	else $organization_id = $GLOBALS['_SESSION_']->customer->organization->id;

	if ($_REQUEST['method'])
	{
		if (! $_REQUEST['name'])
		{
			$GLOBALS['_page']->error = "Name required";
		}
		else
		{
			$parameters = array(
				"name"	=> $_REQUEST['name'],
				"code"	=> $_REQUEST['code'],
				"status"	=> $_REQUEST['status']
			);
			if ($organization_id)
			{
				# Update Existing Organization
				$organization = $_organization->update($organization_id,$parameters);
				
				if ($_organization->error)
				{
					$GLOBALS['_page']->error = "Error updating organization";
				}
				else
				{
					$GLOBALS['_page']->success = "Organization Updated Successfully";
				}
				if ($_REQUEST['new_login'])
				{
					$_customer = new RegisterCustomer();
					
					# Make Sure Login is unique
					$present_customer = $_customer->get($_REQUEST['new_login']);
					if ($present_customer->id)
					{
						$GLOBALS['_page']->error = "Login already exists";
					}
					else
					{
						$customer = $_customer->add(
							array(
								"login"			=> $_REQUEST['new_login'],
								"first_name"	=> $_REQUEST['new_first_name'],
								"last_name"		=> $_REQUEST['new_last_name'],
								"organization_id"	=> $organization_id,
								"password"			=> uniqid()
							)
						);
						if ($_customer->error)
						{
							$GLOBALS['_page']->error = "Error adding customer to organization: ".$_customer->error;
						}
					}
				}
			}
			else
			{
				if (! $parameters['code']) $parameters['code'] = uniqid();
	
				# See if code used
				$present_org = $_organization->get($parameters['code']);
				if ($present_org->id)
				{
					$GLOBALS['_page']->error = "Organization code already used";
				}
				else
				{
					# Add Existing Organization
					$organization = $_organization->add($parameters);
				}
				if ($_organization->error)
				{
					$GLOBALS['_page']->error = "Error updating organization";
				}
				else
				{
					$GLOBALS['_page']->success = "Organization Updated Successfully";
				}
				$organization_id = $organization->id;
			}

		}
	}
	if ($organization_id)
	{
		$organization = $_organization->details($organization_id);
		$members = $_organization->members($organization_id);
		if ($_organization->error)
		{
			$GLOBALS['_page']->error = "Error finding members";
			app_log("Error finding members: ".$_organization->error,'error',__FILE__,__LINE__);
		}
	}
?>
