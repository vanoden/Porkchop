<?php
	$company = array(
		"name"	=> "[null]"
	);

	// Commonly used template files
	$templates = array(
		"default"	=> "default.html",
		"support"	=> "support.html",
		"admin"		=> "admin.html"
	);

	// Configuration for each standard Porkchop module
	$modules = array(
		"Company"		=> array(
			"schema"	=> 4,
			"templates"	=> array(
				"configuration"	=> $templates['admin'],
				"domains"		=> $templates['admin'],
				"domain"		=> $templates['admin'],
				"locations"		=> $templates['admin'],
				"location"		=> $templates['admin'],
			)
		),
		"Site"			=> array(
			"schema"	=> 26,
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
				"term_of_use"	=> $templates['admin'],
				"tou_versions"	=> $templates['admin'],
				"tou_version"	=> $templates['admin'],
				"export_content"	=> $templates['admin'],
				"import_content"	=> $templates['admin'],
				"audit_log"		=> $templates['admin'],
				"header"		=> $templates['admin'],
				"headers"		=> $templates['admin'],
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
			"schema"		=> 47,
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
				"admin_organization"		=> $templates['admin'],
				"accounts"			=> $templates['admin'],
				"admin_account"		=> $templates['admin'],
				"pending_customers"	=> $templates['admin'],
				"roles"				=> $templates['admin'],
				"role"				=> $templates['admin'],
				"admin_location"	=> $templates['admin']
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
		"S4Engine"		=> array(
			"schema"	=> 1
		),
	);

	// Additional modules
	include(MODULES."/product/default/_metadata.php");
	include(MODULES."/sales/default/_metadata.php");
	include(MODULES."/network/default/_metadata.php");
	include(MODULES."/storage/default/_metadata.php");

	$menus = array();

	// Common shipping vendors
	$shipping_vendors = array('Aramex','Australia Post','Bombino','Blue Dart','Canada Post','DB Schenker','Delhivery','DHL','DPD','DTDC','FedEx','Hermes','Nippon','OnTrac Logistics','Parcelforce','PostNL','Purolator','Royal Mail','Spee-dee Delivery','Startrack','TNT','UPS','USPS','Yodel','ZTO Express');
