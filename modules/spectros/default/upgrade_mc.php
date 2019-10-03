<?php
	$modules = array(
		"Company"		=> array(
		),
		"Site"			=> array(
			"roles"			=> array(
				"administrator"	=> array(
					"description"	=> "Super User"
				),
			),
		),
		"Content"		=> array(
			"roles"			=> array(
				"developer"		=> array(
					"description"	=> "Content Developer"
				),
				"operator"		=> array(),
			),
		),
        "Register"		=> array(
			"roles"			=> array(
				"register manager"	=> array(
					"description"		=> "Manager Organizations and Users"
				),
				"register reporter"	=> array(
					"description"		=> "View Organizations and Users"
				),
			),
			"templates"		=> array(
				"organizations"		=> "admin.html",
				"organization"		=> "admin.html",
				"accounts"			=> "admin.html",
				"admin_account"		=> "admin.html",
				"pending_customers"	=> "admin.html",
			),
		),
        "Contact"		=> array(
			"roles"			=> array(
				"contact admin"	=> array(),
			),
		),
        "Storage"		=> array(
			"roles"			=> array(
				"storage manager"	=> "admin.html",
				"storage upload"	=> "admin.html",
			),
			"templates"		=> array(
				"repositories"		=> "admin.html",
				"repository"		=> "admin.html",
				"browse"			=> "admin.html",
			),
		),
		"Media"			=> array(
			"roles"			=> array(
				"media developer"	=> array(),
				"media manager"		=> array(),
				"media reporter"	=> array(),
			),
		),
        "Product"		=> array(
			"roles"			=> array(
				"product manager"	=> array(),
				"product reporter"	=> array(),
			),
			"templates"		=> array(
				"report"			=> "admin.html",
				"edit"				=> "admin.html",
			),
		),
        "Email"			=> array(
			"roles"			=> array(
				"manager"		=> array(),
			),
		),
        "Package"		=> array(
			"roles"			=> array(
				"package manager"	=> array(),
			),
			"templates"		=> array(
				"packages"			=> "admin.html",
				"package"			=> "admin.html",
				"versions"			=> "admin.html",
			),
		),
        "Support"		=> array(
			"roles"			=> array(
			),
			"templates"		=> array(
				"request_new"			=> "admin.html",
				"request_new_monitor"   => "admin.html",
				"requests"			    => "admin.html",
				"request_detail"	    => "admin.html",
				"request_items"		    => "admin.html",
				"request_item"		    => "admin.html",
				"action"			    => "admin.html",
				"admin_actions"		    => "admin.html",
				"pending_registrations"	=> "admin.html",
			),
		),
        "Engineering"	=> array(
			"roles"			=> array(
				"engineering manager"	=> array(),
				"engineering reporter"	=> array(),
			),
			"templates"		=> array(
				"home"				=> "admin.html",
				"tasks"				=> "admin.html",
				"task"				=> "admin.html",
				"releases"			=> "admin.html",
				"release"			=> "admin.html",
				"products"			=> "admin.html",
				"product"			=> "admin.html",
				"projects"			=> "admin.html",
				"project"			=> "admin.html",
				"event_report"		=> "admin.html",
				"search"			=> "admin.html",
			),
		),
		"Action"		=> array(
			"roles"			=> array(
				"action manager"	=> array(),
				"action user"		=> array(),
			),
		),
		"Monitor"		=> array(
			"roles"			=> array(
				"monitor admin"		=> array(),
				"monitor manager"	=> array(),
				"monitor reporter"	=> array(),
				"monitor asset"		=> array(),
			),
			"templates"		=> array(
				"admin_assets"		=> "admin.html",
				"admin_details"		=> "admin.html",
				"comm_dashboard"	=> "admin.html",
			),
		),
	);

	$modules['Spectros'] = array(
		"roles"			=> array(
			"credit manager"	=> "admin.html",
		),
		"templates"		=> array(
			"admin_home"		=> "admin.html",
			"admin_credits"		=> "admin.html",
			"cal_report"		=> "admin.html",
			"transfer_ownership"	=> "admin.html",
			"admin_collections"	=> "admin.html",
		),
	);

	# Show Errors
	error_reporting(E_ERROR);
	ini_set('display_errors',1);

	# Process Modules
	foreach ($modules as $module_name => $module_data) {
		# Update Schema
		$class_name = "\\$module_name\\Schema";
		try {
			$class = new $class_name();
			$class_version = $class->version();
		} catch (Exception $e) {
			install_fail("Cannot upgrade schema '".$class_name."': ".$e->getMessage());
        }
        install_log("$module_name::Schema: version ".$class_version);
		if (isset($module_data['schema_required']) && $module_data['schema_required'] != $class_version) {
			install_fail("Required version ".$module_data['schema_required']." not matched");
		}

		# Add Roles
		foreach ($module_data['roles'] as $role_name => $role_data) {
			$role = new \Register\Role();
			if (! $role->get($role_name)) {
				install_log("Adding role '$role_name'");
				if (! isset($role_data['description'])) $role_data['description'] = $role_name;
				$role->add(array('name' => $role_name,'description' => $role_data['description']));
				if ($role->error) {
					install_fail("Error adding role '$role_name': ".$role->error);
				}
				elseif (isset($role_data['privileges'])) {
					foreach ($role_data['privileges'] as $privilege_name) {
						$role->addPrivilege($privilege_name);
					}
				}
			}
			else {
				install_log("Found role $role_name");
			}
		}
	    install_log("Add new template settings");
	    foreach ($module_data['templates'] as $view => $template) {
			install_log("Add template '$template' to $module_name::$view");
	        $page = new \Site\Page(strtolower($module_name),$view);
	        if ($page->error) {
	            install_fail("Error loading view '$view' for module '$module_name': ".$page->error);
	        }
	        if (! $page->id) {
	            try {
	                $page->add(strtolower($module_name),$view,null);
	            } catch (Exception $e) {
	                install_fail("Cannot add view: ".$e->getMessage());
	            }
				if (! $page->id) {
					install_log("Cannot find view '$view' for module '$module_name': ".$page->error,"warn");
					continue;
				};
			}
			$page->setMetadata("template",$template);
			if ($page->error) {
				install_fail("Could not add metadata to page: ".$page->error);
			}
		}
	}
	exit;

    function install_log($message = '',$level = 'info') {
        print date('Y/m/d H:i:s');
        print " [$level]";
        print ": $message<br>\n";
        flush();
    }

    function install_fail($message) {
        install_log("Upgrade failed: $message",'error');
        exit;
    }
