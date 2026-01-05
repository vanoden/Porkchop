<?php
	$default_Template = "default.html";
	$admin_template = "admin.html";

	$modules = array(
		"Company"		=> array(
		),
		"Site"			=> array(
			"roles"			=> array(
				"administrator"	=> array(
					"description"	=> "Super User"
				),
			),
			"templates" => array(
				"page"	=> $admin_template,
				"pages"	=> $admin_template,
				"configurations"	=> $admin_template,
				"send_customer_message" => $admin_template
			),
		),
		"Geography"		=> array(
			"roles"			=> array(
			),
		),
		"Content"		=> array(
			"roles"			=> array(
				"Content Developer"		=> array(
					"description"	=> "Content Developer"
				),
				"Content Operator"		=> array(),
			),
		),
        "Register"		=> array(
			"roles"			=> array(
			),
			"templates"		=> array(
				"organizations"		=> $admin_template,
				"organization"		=> $admin_template,
				"accounts"			=> $admin_template,
				"admin_account"		=> $admin_template,
				"admin_account_contacts"	=> $admin_template,
				"admin_account_password"	=> $admin_template,
				"admin_account_roles"		=> $admin_template,
				"admin_account_auth_failures"	=> $admin_template,
				"admin_account_terms"		=> $admin_template,
				"admin_account_locations"	=> $admin_template,
				"admin_account_images"		=> $admin_template,
				"admin_account_backup_codes"	=> $admin_template,
				"admin_account_search_tags"	=> $admin_template,
				"pending_customers"	=> $admin_template,
				"roles"				=> $admin_template,
				"role"				=> $admin_template,
				"audit_log"			=> $admin_template
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
			),
			"templates"		=> array(
				"repositories"		=> $admin_template,
				"repository"		=> $admin_template,
				"browse"			=> $admin_template,
			),
		),
		"Media"			=> array(
			"roles"			=> array(
			),
		),
        "Product"		=> array(
			"roles"			=> array(
			),
			"templates"		=> array(
				"report"			=> $admin_template,
				"edit"				=> $admin_template,
			),
		),
        "Email"			=> array(
			"roles"			=> array(
			),
		),
		"Monitor"		=> array(
			"roles"			=> array(
				"monitor asset"		=> array(),
			),
			"templates"		=> array(
				"admin_assets"		=> $admin_template,
				"admin_details"		=> $admin_template,
				"comm_dashboard"	=> $admin_template,
				"sensor_models"		=> $admin_template,
				"sensor_model"		=> $admin_template,
				"dashboards"		=> $admin_template,
				"admin_dashboard"	=> $admin_template
			),
		)
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
				)
			)
		),
		"admin"	=> array(
			"title"	=> "Admin Left Nav",
			"items"	=> array(
			    array(
				    "title"			=> "Overview",
				    "target"		=> "/_spectros/outstanding_requests",
				    "view_order"	=> 1,
				    "alt"			=> "Outstanding Task Tickler",
				    "description"	=> "Outstanding Task Tickler"
			    ),				
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
							"title"			=> "Duplicate Report",
							"target"		=> "/_register/organizations_report",
							"view_order"	=> 15,
							"alt"			=> "Organizations Duplicate Report",
							"description"	=> "Find and manage duplicate organizations"
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
					"title"			=> "Warehouse",
					"view_order"	=> 18,
					"alt"			=> "Warehouse Functions",
					"description"	=> "Warehouse Management",
					"items"			=> array(
						array(
							"title"		=> "Shipments",
							"target"	=> "/_shipping/admin_shipments",
							"alt"		=> "Shipments",
							"description"	=> "Manage shipments"
						),
					),
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
							"target"	=> "/_support/admin_rmas",
							"view_order"	=> 90,
							"alt"			=> "RMAs",
							"description"	=> "RMAs"
						),
						array (
							"title"	=> "Summary",
							"target"	=> "/_support/summary",
							"view_order"	=> 100,
							"alt"			=> "Summary",
							"description"	=> "Summary"
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
					"title"			=> "Dashboard",
					"target"		=> "/_spectros/outstanding_requests",    
					"view_order"	=> 50,
					"alt"			=> "Personal Dashboard",
					"description"	=> "Personal Dashboard"
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
							"view_order"	=> 10,
							"alt"			=> "Page Management",
							"description"	=> "Page Management"
						),
						array (
							"title"	=> "Configurations",
							"target"	=> "/_site/configurations",
							"view_order"	=> 20,
							"alt"			=> "Site Configurations",
							"description"	=> "Site Configurations"
						),
						array (
							"title" => "HTTP Headers",
							"target"	=> "/_site/headers",
							"view_order"	=> 30,
							"alt"			=> "HTTP Headers",
							"description"	=> "HTTP Headers"
						),
						array (
							"title"	=> "Site Counters",
							"target"	=> "/_site/counters",
							"view_order"	=> 40,
							"alt"			=> "Site Counters",
							"description"	=> "Site Counters"
						),
						array (
							"title" => "Navigation",
							"target"	=> "/_navigation/menus",
							"view_order"	=> 50,
							"alt"			=> "Navigation",
							"description"	=> "Navigation"
						),
						array (
							"title" => "Terms of Use",
							"target"	=> "/_site/terms_of_use",
							"view_order"	=> 60,
							"alt"			=> "Terms of Use",
							"description"	=> "Terms of Use"
						),
						array (
							"title" => "Audit Log",
							"target"	=> "/_site/audit_log",
							"view_order"	=> 70,
							"alt"			=> "Audit Log",
							"description"	=> "Audit Log",
							"required_role"	=> "administrator"
						),
						array (
							"title"	=> "API Sessions",
							"target"	=> "/_monitor/comm_dashboard",
							"view_order"	=> 90,
							"alt"			=> "Session Report",
							"description"	=> "Session Report"
						),					
					)
				)
			)
		)
	);

	# Show Errors
	error_reporting(E_ERROR);
	ini_set('display_errors',1);

	if ($_REQUEST['log_level']) $log_level = $_REQUEST['log_level'];

	install_log("Starting site upgrade",'notice');
	if (defined('HTML') && file_exists(HTML."/version.txt")) {
		install_log("Loaded ".HTML."/version.txt",'debug');
		$version_info = file_get_contents(HTML."/version.txt");
		if (preg_match('/PRODUCT\:\s(.+)/',$version_info,$matches)) install_log("Product: ".$matches[1],'notice');
		if (preg_match('/BUILD_ID\:\s(.+)/',$version_info,$matches)) install_log("Build: ".$matches[1]);
		if (preg_match('/BUILD_DATE\:\s(.+)/',$version_info,$matches)) install_log("Built: ".$matches[1]);
		if (preg_match('/VERSION\:\s(.+)/',$version_info,$matches)) install_log("Version: ".$matches[1],'notice');
	}
	else {
		install_log("No version.txt found or HTML constant not defined",'warning');
	}

	# Process Modules
	foreach ($modules as $module_name => $module_data) {
		install_log($module_name);
		# Update Schema
		$class_name = "\\$module_name\\Schema";
		try {
			$class = new $class_name();
			$class_version = $class->version();
			if (! $class->upgrade()) {
				install_fail("Error upgrading $module_name schema: ".$class->error());
			}
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
				if ($role->error()) {
					install_fail("Error adding role '$role_name': ".$role->error());
				}
				elseif (isset($role_data['privileges'])) {
					foreach ($role_data['privileges'] as $privilege_name) {
						$role->addPrivilege($privilege_name);
					}
				}
			}
			else {
				install_log("Found role $role_name",'debug');
			}
		}
	    install_log("Add new template settings");
	    foreach ($module_data['templates'] as $view => $template) {
	        $page = new \Site\Page(strtolower($module_name),$view);
	        if ($page->error()) {
	            install_fail("Error loading view '$view' for module '$module_name': ".$page->error());
	        }
	        if (! $page->id) {
	            try {
	                $page->add(strtolower($module_name),$view,null);
	            } catch (Exception $e) {
	                install_fail("Cannot add view: ".$e->getMessage());
	            }
				if (! $page->id) {
					install_log("Cannot find view '$view' for module '$module_name': ".$page->error(),"warn");
					continue;
				};
			}

			if ($page->metadata->template != $template) {
				install_log("Add template '$template' to $module_name::$view");
				$page->setMetadata("template",$template);
				if ($page->error()) {
					install_fail("Could not add metadata to page: ".$page->error());
				}
			}
			else {
				install_log("Template already set correctly for $module_name::$view",'trace');
			}
		}
	}

	foreach ($menus as $code => $menu) {
		$nav_menu = new \Site\Navigation\Menu();
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
			$nav_item = new \Site\Navigation\Item();
			if ($nav_item->get($nav_menu->id,$item["title"])) {
				$nav_item->update(
					array(
						"view_order"	=> $item["view_order"],
						"alt"			=> $item["alt"],
						"description"	=> $item["description"],
						"target"		=> $item["target"],
					)
				);
				install_log("Menu Item ".$item["title"]." updated");
			}
			elseif (! $nav_item->error() && $nav_item->add(
					array(
						"menu_id"		=> $nav_menu->id,
						"title"			=> $item["title"],
						"target"		=> $item["target"],
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
				$subnav_item = new \Site\Navigation\Item();
				if ($subnav_item->get($nav_menu->id,$subitem["title"],$nav_item)) {
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
	install_log("Upgrade completed successfully",'notice');
	exit;

    function install_log($message = '',$level = 'info') {
		if (! log_level($level)) return;
        print date('Y/m/d H:i:s');
        print " [$level]";
        print ": $message<br>\n";
        flush();
    }

    function install_fail($message) {
        install_log("Upgrade failed: $message",'error');
        exit;
    }

	function log_level($level = 'info') {
		if ($_REQUEST['log_level']) $log_level = $_REQUEST['log_level'];
		else $log_level = 'warning';

		if ($log_level == 'trace') return true;
		if ($log_level == 'debug' && $level != 'trace') return true;
		if ($log_level == 'info' && $level != 'trace' && $level != 'debug') return true;
		if ($log_level == 'warning' && $level != 'trace' && $level != 'debug' && $level != 'info') return true;
		if ($log_level == 'notice' && $level != 'trace' && $level != 'debug' && $level != 'info' && $level != 'warning') return true;
		if ($log_level == 'error') return true;
		return false;
	}
