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
			"schema"	=> 33,
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
			"schema"		=> 54,
			"privileges"	=> array(
				"manage privileges",
				"manage customers",
				"manage organization comments",
                "manage customer locations",
				"see admin tools",
				"see register api"
			),
			"templates"		=> array(
				"organizations_report"	=> $templates['admin'],
				"admin_organizations"	=> $templates['admin'],
				"admin_organization"		=> $templates['admin'],
				"admin_organization_users"	=> $templates['admin'],
				"admin_organization_tags"	=> $templates['admin'],
				"admin_organization_locations"	=> $templates['admin'],
				"admin_organization_audit_log"	=> $templates['admin'],
				"admin_account"		=> $templates['admin'],
				"pending_customers"	=> $templates['admin'],
				"privileges"		=> $templates['admin'],
				"roles"				=> $templates['admin'],
				"role"				=> $templates['admin'],
				"admin_location"	=> $templates['admin'],
				"admin_accounts"		=> $templates['admin'],
				"admin_account_contacts"	=> $templates['admin'],
				"admin_account_password"	=> $templates['admin'],
				"admin_account_roles"		=> $templates['admin'],
				"admin_account_auth_failures"	=> $templates['admin'],
				"admin_account_terms"		=> $templates['admin'],
				"admin_account_locations"	=> $templates['admin'],
				"admin_account_images"		=> $templates['admin'],
				"admin_account_backup_codes"	=> $templates['admin'],
				"admin_account_search_tags"	=> $templates['admin'],
				"admin_account_audit_log"	=> $templates['admin'],
				"admin_account_register_audit"	=> $templates['admin'],
				"ent_accounts"			=> $templates['support']
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
			"schema"	=> 3
		),
		"Search"		=> array(
			"schema"	=> 3
		),
		"Engineering"	=> array(
			"templates"	=> array(
				"home"			=> $templates['admin'],
				"tasks"			=> $templates['admin'],
				"task"			=> $templates['admin'],
				"releases"		=> $templates['admin'],
				"release"		=> $templates['admin'],
				"projects"		=> $templates['admin'],
				"project"		=> $templates['admin'],
				"products"		=> $templates['admin'],
				"product"		=> $templates['admin'],
				"event_report"	=> $templates['admin'],
				"search"		=> $templates['admin'],
			)
		),
		"Support"		=> array(
			"templates"	=> array(
				"request_new"	=> $templates['admin'],
				"requests"		=> $templates['admin'],
				"request_detail"	=> $templates['admin'],
				"request_items"	=> $templates['admin'],
				"request_item"	=> $templates['admin'],
				"action"		=> $templates['admin'],
				"admin_actions"	=> $templates['admin'],
				"summary"		=> $templates['admin'],
				"admin_rmas"	=> $templates['admin'],
				"admin_rma"		=> $templates['admin'],
			)
		),
		"Monitor"		=> array(
			"templates"	=> array(
				"admin_assets"	=> $templates['admin'],
				"admin_details"	=> $templates['admin'],
				"admin_collections"	=> $templates['admin'],
				"comm_dashboard"	=> $templates['admin'],
				"sensor_models"	=> $templates['admin'],
				"sensor_model"	=> $templates['admin'],
				"dashboards"	=> $templates['admin'],
				"admin_dashboard"	=> $templates['admin'],
			)
		),
	);

	// Additional modules
	include(MODULES."/product/default/_metadata.php");
	include(MODULES."/sales/default/_metadata.php");
	include(MODULES."/network/default/_metadata.php");
	include(MODULES."/storage/default/_metadata.php");
	include(MODULES."/shipping/default/_metadata.php");
	include(MODULES."/package/default/_metadata.php");
	include(MODULES."/support/default/_metadata.php");

	$menus = array();
	// Include upgrade.local.php from any module that has one (e.g. site, spectros)
	foreach (array_keys($modules) as $module) {
		$path = MODULES."/".strtolower($module)."/default/upgrade.local.php";
		if (file_exists($path)) {
			include($path);
		}
	}

	// Common shipping vendors
	$shipping_vendors = array('Aramex','Australia Post','Bombino','Blue Dart','Canada Post','DB Schenker','Delhivery','DHL','DPD','DTDC','FedEx','Hermes','Nippon','OnTrac Logistics','Parcelforce','PostNL','Purolator','Royal Mail','Spee-dee Delivery','Startrack','TNT','UPS','USPS','Yodel','ZTO Express');
