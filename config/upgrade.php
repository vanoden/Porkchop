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
			"privileges"	=> array(
				'send admin in-site message',
				'edit site pages',
				'configure site'
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
			"privileges"	=> array(
				'manage geographical data'
			),
		),
		"Content"		=> array(
			"schema"	=> 3,
			"privileges"	=> array(
				"edit content messages",
				"browse content messages",
				"edit page metadata"
			),
		),
		"Register"		=> array(
			"schema"		=> 21,
			"privileges"	=> array(
				"manage privileges",
				"manage customers",
				"manage organization comments",
                "manage customer locations"
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
			"privileges"	=> array(
				"manage contacts",
				"browse contact events"
            )
		),
		"Navigation"	=> array(
            "privileges"    => array(
                "manage navigation menus"
            )
        ),
		"Product"		=> array(
			"schema"	=> 5,
			"privileges"	=> array(
                "edit product prices",
                "manage products",
                "manage product instances"
            ),
			"templates"		=> array(
				"report"			=> $templates['admin'],
				"edit"				=> $templates['admin'],
			),
		),
		"Email"			=> array(
			"schema"	=> 2,
            "privileges"    => array(
                "can create email"
            )
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
