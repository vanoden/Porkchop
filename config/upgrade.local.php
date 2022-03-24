<?php
	$company["name"] = "Spectros Instruments";

	$templates['monitor'] = 'monitor.html';

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
					"view_order"	=> 5,
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
						),
						array (
							"title"	=> "Dashboards",
							"target"	=> "/_monitor/dashboards",
							"view_order"	=> 95,
							"alt"			=> "Monitor Dashboard Management",
							"description"	=> "Monitor Dashboard Management"
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
							"title"	=> "Configurations",
							"target"	=> "/_site/configurations",
							"view_order"	=> 20,
							"alt"			=> "Site Configurations",
							"description"	=> "Site Configurations"
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

	$modules["Monitor"]	= array(
		"schema"	=> 28,
		"roles"			=> array(
			"monitor admin"		=> array(),
			"monitor manager"	=> array(),
			"monitor reporter"	=> array(),
			"monitor asset"		=> array(),
		),
		"templates"		=> array(
			"admin_assets"		=> $templates['admin'],
			"admin_details"		=> $templates['admin'],
			"comm_dashboard"	=> $templates['admin'],
			"sensor_models"		=> $templates['admin'],
			"sensor_model"		=> $templates['admin'],
			"dashboards"		=> $templates['admin'],
			"admin_dashboard"	=> $templates['admin']
		),
	);
	$modules['Engineering']['roles']['Systems Administrator']	= array("description" => 'Linux, Web Administration');
	$modules['Engineering']['roles']['Graphic Designer']		= array("description" => 'Graphic Designer');
	$modules['Engineering']['roles']['Web Programmer']			= array("description" => 'PHP, Javascript Coding');
	$modules['Engineering']['roles']['Embedded Programmer']		= array("description" => 'Perl, Python, C++ Coding');
	$modules['Engineering']['roles']['QA Automation']			= array("description" => 'QA Automation');

	$modules["Alert"] = array(
		"schema"	=> 6,
		"roles"		=> array(
			'alert manager'     => array(),
			'alert reporter'    => array(),
			'alert admin'       => array(),
			'alert asset'       => array()
		),
		"templates"	=> array()
	);

	$modules['Spectros'] = array(
		"schema"	=> 10,
		"roles"			=> array(
			"credit manager"	=> $templates['admin'],
		),
		"templates"		=> array(
			"admin_home"		=> $templates['admin'],
			"admin_credits"		=> $templates['admin'],
			"cal_report"		=> $templates['admin'],
			"transfer_ownership"	=> $templates['admin'],
			"admin_collections"	=> $templates['admin'],
		),
	);

	$shipping_vendors = array('DHL','FedEx','UPS','USPS');
