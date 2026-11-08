<?php
	$modules["Email"] = array(
		"schema"	=> 3,
		"privileges"    => array(
			"create email",
			"see email api"
		),
		"templates"	=> array(
			"admin_messages"	=> $templates['admin'],
			"admin_deliveries"	=> $templates['admin'],
			"admin_attempts"	=> $templates['admin'],
		),
	);