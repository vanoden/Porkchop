<?php
	$modules["Network"]	= array(
		"schema"	=> 8,
		"templates"	=> array(
			"admin_subnets"	=> $templates['admin'],
			"admin_subnet"	=> $templates['admin'],
			"admin_hosts"		=> $templates['admin'],
			"admin_host"		=> $templates['admin']
		),
		"privileges"	=> array(
			"manage subnets",
			"manage hosts",
			"manage acls"
		)
	);

	$menus["Network"] = array(
		"Subnets"	=> "/_network/admin_subnets"
	);