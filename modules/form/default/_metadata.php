<?php
	$modules['Form'] = array(
		"schema"    => 2,
		"privileges"	=> array(
			"manage forms"
		),
		'templates'		=> array(
			"admin_forms" => $templates['admin'],
			"admin_form" => $templates['admin'],
			"admin_version" => $templates['admin'],
		)
	);