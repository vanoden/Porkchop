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
				"roles"				=> "admin.html",
				"role"				=> "admin.html"
			),
		),
        "Contact"		=> array(
			"roles"			=> array(
				"contact admin"	=> array(),
			),
		),
		"Navigation"	=> array(),
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
				"sensor_models"		=> "admin.html",
				"sensor_model"		=> "admin.html"
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

	$menus = array(
		"welcome" => array(
			"title"	=> "Welcome",
			"items"	=> array(
				array(
					"title" 		=> "My Account",
					"target"		=> "/_register/account",
					"alt"			=> "My Account",
					"view_order"	=> 10,
					"description"	=> "View/edit your account, password and contact settings."
				),
				array(
					"title"			=> "Jobs",
					"target"		=> "/_monitor/collections",
					"view_order"	=> 20,
					"alt"			=> "Jobs",
					"description"	=> "See or Create Fumigation Jobs"
				)
			)
		),
		"admin"	=> array(
			"title"	=> "Admin Left Nav",
			"items"	=> array(
				array(
					"title"			=> "Customer",
					"view_order"	=> 10,
					"alt"			=> "Customer",
					"description"	=> "Customer Management Tools",
					"items" 		=> array(
						array (
							"title"			=> "Organizations",
							"target"		=> "/_register/organizations",
							"view_order"	=> 10,
							"alt"			=> "Organizations",
							"description"	=> "Organizations"
						),
						array (
							"title"			=> "Accounts",
							"target"		=> "/_register/accounts",
							"view_order"	=> 20,
							"alt"			=> "Accounts",
							"description"	=> "Account Management"
						),
						array (
							"title"	=> "Pending",
							"target"	=> "/_register/pending_customers",
							"view_order"	=> 30,
							"alt"			=> "Pending Registrations",
							"description"	=> "Pending Registrations"
						),
						array (
							"title"	=> "Roles",
							"target"	=> "/_register/roles",
							"view_order"	=> 70,
							"alt"			=> "Role Management",
							"description"	=> "Role Management"
						)
					)
				),
				array(
					"title"			=> "Products",
					"target"		=> "/_product/report",
					"view_order"	=> 10,
					"alt"			=> "Product Management",
					"description"	=> "Product Management"
				),
				array(
					"title"			=> "Datalogger",
					"view_order"	=> 20,
					"alt"			=> "Datalogger",
					"description"	=> "Datalogger Management",
					"items"			=> array(
						array (
							"title"	=> "Monitors",
							"target"	=> "/_monitor/admin_assets",
							"view_order"	=> 10,
							"alt"			=> "Monitors",
							"description"	=> "Monitors"
						),
						array (
							"title"	=> "Jobs",
							"target"	=> "/_monitor/admin_collections",
							"view_order"	=> 10,
							"alt"			=> "Jobs",
							"description"	=> "Collection Management"
						),
						array (
							"title"	=> "Credits",
							"target"	=> "/_spectros/admin_credits",
							"view_order"	=> 10,
							"alt"			=> "Calibration Credits",
							"description"	=> "Calibration Credit Management"
						),
						array (
							"title"	=> "Calibrations",
							"target"	=> "/_spectros/cal_report",
							"view_order"	=> 40,
							"alt"			=> "Calibration Report",
							"description"	=> "Calibration Report"
						),
						array (
							"title"	=> "Transfer Device",
							"target"	=> "/_spectros/transfer_ownership",
							"view_order"	=> 50,
							"alt"			=> "Transfer Device",
							"description"	=> "Device Transfers"
						),
						array (
							"title"	=> "Sensor Models",
							"target"	=> "/_monitor/sensor_models",
							"view_order"	=> 90,
							"alt"			=> "Sensor Model Management",
							"description"	=> "Sensor Model Management"
						)
					)
				),
				array(
					"title"			=> "Engineering",
					"target"		=> "/_engineering/home",
					"view_order"	=> 50,
					"alt"			=> "Engineering Module",
					"description"	=> "Engineering Module",
					"items"			=> array(
						array (
							"title"	=> "Home",
							"target"	=> "/_engineering/home",
							"view_order"	=> 1,
							"alt"			=> "Engineering Home",
							"description"	=> "Engineering Summary Page"
						),
						array (
							"title"	=> "Tasks",
							"target"	=> "/_engineering/tasks",
							"view_order"	=> 10,
							"alt"			=> "Task Management",
							"description"	=> "Task Management"
						),
						array (
							"title"	=> "Releases",
							"target"	=> "/_engineering/releases",
							"view_order"	=> 10,
							"alt"			=> "Release Management",
							"description"	=> "Release Management"
						),
						array (
							"title"	=> "Projects",
							"target"	=> "/_engineering/projects",
							"view_order"	=> 10,
							"alt"			=> "Project Management",
							"description"	=> "Project Management"
						),
						array (
							"title"	=> "Products",
							"target"	=> "/_engineering/products",
							"view_order"	=> 10,
							"alt"			=> "Product Management",
							"description"	=> "Product Management"
						),
						array (
							"title"	=> "Report",
							"target"	=> "/_engineering/event_report",
							"view_order"	=> 70,
							"alt"			=> "Event Report",
							"description"	=> "Event Report"
						)
					)
				),
				array(
					"title"			=> "Support",
					"view_order"	=> 60,
					"alt"			=> "Support Home",
					"description"	=> "Support Home",
					"items"			=> array(
						array (
							"title"	=> "New Request",
							"target"	=> "/_support/request_new",
							"view_order"	=> 10,
							"alt"			=> "New Request",
							"description"	=> "New Support Request"
						),
						array (
							"title"	=> "Requests",
							"target"	=> "/_support/requests",
							"view_order"	=> 10,
							"alt"			=> "Requests",
							"description"	=> "Requests"
						),
						array (
							"title"	=> "Tickets",
							"target"	=> "/_support/request_items",
							"view_order"	=> 30,
							"alt"			=> "Support Tickets",
							"description"	=> "Support Tickets"
						),
						array (
							"title"	=> "Actions",
							"target"	=> "/_support/admin_actions",
							"view_order"	=> 40,
							"alt"			=> "Support Actions",
							"description"	=> "Support Actions"
						),
						array (
							"title"	=> "RMAs",
							"target"	=> "/_support/rmas",
							"view_order"	=> 90,
							"alt"			=> "RMAs",
							"description"	=> "RMAs"
						)
					)
				),
				array(
					"title"			=> "Storage",
					"view_order"	=> 70,
					"alt"			=> "Storage Management",
					"description"	=> "Storage Management",
					"items"			=> array(
						array (
							"title"	=> "Repositories",
							"target"	=> "/_storage/repositories",
							"view_order"	=> 10,
							"alt"			=> "Storage Repositories",
							"description"	=> "Storage Repositories"
						)
					)
				),
				array(
					"title"			=> "Packages",
					"target"		=> "/_package/packages",
					"view_order"	=> 80,
					"alt"			=> "Package Management",
					"description"	=> "Package Management"
				),
				array(
					"title"			=> "Site",
					"view_order"	=> 80,
					"alt"			=> "Site Management",
					"description"	=> "Site Management",
					"items"			=> array(
						array (
							"title"	=> "Pages",
							"target"	=> "/_site/pages",
							"view_order"	=> 20,
							"alt"			=> "Page Management",
							"description"	=> "Page Management"
						),
						array (
							"title"	=> "API Sessions",
							"target"	=> "/_monitor/comm_dashboard",
							"view_order"	=> 20,
							"alt"			=> "Session Report",
							"description"	=> "Session Report"
						)
					)
				)
			)
		)
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
	
	foreach ($menus as $code => $menu) {
		$nav_menu = new \Navigation\Menu();
		if ($nav_menu->get($code)) {
			install_log("Menu $code found");
		}
		elseif (! $nav_menu->error() && $nav_menu->add(array("code" => $code,"title" => $menu["title"]))) {
			install_log("Menu $code added");
		}
		else {
			install_fail("Error adding menu $code: ".$nav_menu->error());
		}
		foreach ($menu["items"] as $item) {
			$nav_item = new \Navigation\Item();
			if ($nav_item->get($nav_menu->id,$item["title"])) {
				$nav_item->update(
					array(
						"view_order"	=> $item["view_order"],
						"alt"			=> $item["alt"],
						"description"	=> $item["description"]
					)
				);
				install_log("Menu Item ".$item["title"]." updated");
			}
			elseif (! $nav_item->error() && $nav_item->add(
					array(
						"menu_id"		=> $nav_menu->id,
						"title"			=> $item["title"],
						"view_order"	=> $item["view_order"],
						"alt"			=> $item["alt"],
						"description"	=> $item["description"]
					)
				)) {
					install_log("Adding Menu Item ".$item["title"]);
			}
			else {
				install_fail("Error adding menu item ".$item["title"].": ".$nav_item->error());
			}
			foreach ($item['items'] as $subitem) {
				$subnav_item = new \Navigation\Item();
				if ($subnav_item->get($nav_menu->id,$subitem["title"])) {
					$subnav_item->update(
						array(
							"view_order"	=> $subitem["view_order"],
							"target"		=> $subitem["target"],
							"alt"			=> $subitem["alt"],
							"description"	=> $subitem["description"]
						)
					);
					install_log("Sub Menu Item ".$subitem["title"]." updated");
				}
				elseif (! $subnav_item->error() && $subnav_item->add(
						array(
							"menu_id"		=> $nav_menu->id,
							"parent_id"		=> $nav_item->id,
							"title"			=> $subitem["title"],
							"target"		=> $subitem["target"],
							"view_order"	=> $subitem["view_order"],
							"alt"			=> $subitem["alt"],
							"description"	=> $subitem["description"]
						)
					)) {
						install_log("Adding SubMenu Item ".$subitem["title"]);
				}
				else {
					install_fail("Error adding menu item ".$subitem["title"].": ".$subnav_item->error());
				}
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
