<?php
	$menus = array(
		(object) array(
			"label"			=> "My Account",
			"description"	=> "Change your Settings, Contact Information or Password",
			"target"		=> "/_register/account/".$GLOBALS['_SESSION_']->customer->login
		),
		(object) array(
			"label"			=> "Monitor Portal",
			"description"	=> "Create and Monitor Jobs, View and Calibrate Monitors.",
			"target"		=> "/_monitor/assets"
		),
		(object) array(
			"label"			=> "Utility Downloads",
			"description"	=> "Desktop Applications for Calibration and Configuration",
			"target"		=> "http://s3.amazonaws.com/spectros-public-assets/index.html"
		)
	);

	if (role('register manager') || role('monitor admin') || role('action user')) {
		array_push($menus,
			(object) array(
				"label"			=> "Site Administration",
				"description"	=> "Administrative Tools",
				"target"		=> "/_admin/admin_home"
			)
		);
	}
