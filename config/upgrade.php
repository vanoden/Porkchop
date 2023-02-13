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
			"schema"	=> 16,
			"privileges"	=> array(
				'send admin in-site message',
				'edit site pages',
				'configure site',
				'see site api',
				'manage terms of use'
			),
			"templates"	=> array(
				"page"	=> $templates['admin'],
				"pages"	=> $templates['admin'],
				"configurations"	=> $templates['admin'],
				"counters"	=> $templates['admin'],
				"content_block"	=> $templates['admin'],
				"terms_of_use"	=> $templates['admin'],
				"term_of_use"	=> $templates['admin']
			),
		),
		"Geography"		=> array(
			"schema"		=> 1,
			"roles"			=> array(
				"geography manager"	=> array(),
				"geography user"	=> array(),
			),
			"privileges"	=> array(
				'manage geographical data',
				'see geography api'
			),
		),
		"Content"		=> array(
			"schema"	=> 3,
			"privileges"	=> array(
				"edit content messages",
				"browse content messages",
				"edit page metadata",
				"see content api"
			),
		),
		"Register"		=> array(
			"schema"		=> 29,
			"privileges"	=> array(
				"manage privileges",
				"manage customers",
				"manage organization comments",
                "manage customer locations",
				"see admin tools",
				"see register api"
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
				"browse contact events",
				"see contact api"
            )
		),
		"Navigation"	=> array(
            "privileges"    => array(
                "manage navigation menus",
				"see navigation api"
			),
			"templates" => array(
				"menus"		=> $templates['admin'],
				"items"		=> $templates['admin'],
				"item"		=> $templates['admin']
			)
        ),
		"Product"		=> array(
			"schema"	=> 5,
			"privileges"	=> array(
                "edit product prices",
                "manage products",
                "manage product instances",
				"add product instances",
				"see product api"
            ),
			"templates"		=> array(
				"report"			=> $templates['admin'],
				"edit"				=> $templates['admin'],
			),
		),
		"Email"			=> array(
			"schema"	=> 2,
            "privileges"    => array(
                "create email",
				"see email api"
            )
		),
		"Action"		=> array(
			"schema"	=> 1,
			"roles"			=> array(
				"action manager"	=> array(),
				"action user"		=> array(),
			),
		),
		"Network"		=> array(
			"schema"	=> 2,
			"privileges"	=> array(
				"manage subnets",
				"manage hosts",
				"manage acls"
			)
		)
	);

	$menus = array();

	$shipping_vendors = array('DHL','FedEx','UPS','USPS');
