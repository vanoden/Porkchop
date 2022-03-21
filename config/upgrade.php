<?php
	$company = array(
		"name"	=> "[null]"
	);

	$templates = array(
		"default"	=> "default.html",
		"admin"		=> "admin.html"
	);

	$modules = array(
		"Company"		=> array(
			"schema"	=> 3,
		),
		"Site"			=> array(
			"schema"	=> 8,
			"roles"			=> array(
				"administrator"	=> array(
					"description"	=> "Super User"
				),
			),
			"templates"	=> array(
				"page"	=> $templates['admin'],
				"pages"	=> $templates['admin'],
				"configurations"	=> $templates['admin']
			),
		),
		"Geography"		=> array(
			"schema"		=> 1,
			"roles"			=> array(
				"geography manager"	=> array(),
				"geography user"	=> array(),
			),
		),
		"Content"		=> array(
			"schema"	=> 3,
			"roles"			=> array(
				"developer"		=> array(
					"description"	=> "Content Developer"
				),
				"operator"		=> array(),
			),
		),
		"Register"		=> array(
			"schema"		=> 20,
			"roles"			=> array(
				"register manager"	=> array(
					"description"		=> "Manager Organizations and Users"
				),
				"register reporter"	=> array(
					"description"		=> "View Organizations and Users"
				),
			),
			"templates"		=> array(
				"organizations"		=> $templates['admin'],
				"organization"		=> $templates['admin'],
				"accounts"			=> $templates['admin'],
				"admin_account"		=> $templates['admin'],
				"pending_customers"	=> $templates['admin'],
				"roles"				=> $templates['admin'],
				"role"				=> $templates['admin'],
				"admin_location"	=> $templates['admin'],
			),
		),
		"Contact"		=> array(
			"schema"	=> 2,
			"roles"			=> array(
				"contact admin"	=> array(),
			),
		),
		"Navigation"	=> array(),
		"Build"		=> array(
			"roles"		=> array(
				"build manager"		=> "Manage Build Products and Repositories",
				"build user"		=> "Create versions and commits",
			),
			"templates"	=> array(
				"products"			=> $templates['admin'],
				"product"			=> $templates['admin'],
				"product_new"		=> $templates['admin'],
				"versions"			=> $templates['admin'],
				"version"			=> $templates['admin'],
			),
		),
		"Storage"		=> array(
			"schema"	=> 5,
			"roles"			=> array(
				"storage manager"	=> $templates['admin'],
				"storage upload"	=> $templates['admin'],
			),
			"templates"		=> array(
				"repositories"		=> $templates['admin'],
				"repository"		=> $templates['admin'],
				"browse"			=> $templates['admin'],
			),
		),
		"Media"			=> array(
			"schema"	=> 3,
			"roles"			=> array(
				"media developer"	=> array(),
				"media manager"		=> array(),
				"media reporter"	=> array(),
			),
		),
		"Sales"			=> array(
			"schema"	=> 7,
			"roles"			=> array(
				"sales manager"	=> array(),
			),
		),
		"Product"		=> array(
			"schema"	=> 5,
			"roles"			=> array(
				"product manager"	=> array(),
				"product reporter"	=> array(),
			),
			"templates"		=> array(
				"report"			=> $templates['admin'],
				"edit"				=> $templates['admin'],
			),
		),
		"Email"			=> array(
			"schema"	=> 2,
			"roles"			=> array(
				"manager"		=> array(),
			),
		),
		"Package"		=> array(
			"schema"	=>2,
			"roles"			=> array(
				"package manager"	=> array(),
			),
			"templates"		=> array(
				"packages"			=> $templates['admin'],
				"package"			=> $templates['admin'],
				"versions"			=> $templates['admin'],
			),
		),
		"Shipping"		=> array(
			"schema"	=> 3,
			"roles"			=> array(
				"shipping manager"	=> array(),
			),
			"templates"		=> array(
				"admin_shipments"			=> $templates['admin'],
				"admin_shipment"			=> $templates['admin'],
			),
		),
		"Support"		=> array(
			"schema"	=> 8,
			"roles"			=> array(
				"support manager"	=> array(),
				"support reporter"	=> array(),
				"support user"		=> array(),
			),
			"templates"		=> array(
				"request_new"			=> $templates['admin'],
				"request_new_monitor"   => $templates['admin'],
				"requests"			    => $templates['admin'],
				"request_detail"	    => $templates['admin'],
				"request_items"		    => $templates['admin'],
				"request_item"		    => $templates['admin'],
				"action"			    => $templates['admin'],
				"admin_actions"		    => $templates['admin'],
				"pending_registrations"	=> $templates['admin'],
				"admin_rmas"			=> $templates['admin'],
				"admin_rma"				=> $templates['admin'],
				"summary"   			=> $templates['admin'],
				"tickets"				=> "monitor.html",
				"ticket"				=> "monitor.html"
			),
		),
		"Engineering"	=> array(
			"schema"	=>12,
			"roles"			=> array(
				"engineering manager"	=> array(),
				"engineering reporter"	=> array(),
			),
			"templates"		=> array(
				"home"				=> $templates['admin'],
				"tasks"				=> $templates['admin'],
				"task"				=> $templates['admin'],
				"releases"			=> $templates['admin'],
				"release"			=> $templates['admin'],
				"products"			=> $templates['admin'],
				"product"			=> $templates['admin'],
				"projects"			=> $templates['admin'],
				"project"			=> $templates['admin'],
				"event_report"		=> $templates['admin'],
				"search"			=> $templates['admin'],
			),
		),
		"Action"		=> array(
			"schema"	=> 1,
			"roles"			=> array(
				"action manager"	=> array(),
				"action user"		=> array(),
			),
		)
	);

	$menus = array();

	$shipping_vendors = array('DHL','FedEx','UPS','USPS');