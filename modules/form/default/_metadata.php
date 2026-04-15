<?php
	$modules['Form'] = array(
		"schema"    => 4,
		"privileges"	=> array(
			"manage forms"
		),
		'templates'		=> array(
			"admin_forms" => $templates['admin'],
			"admin_form" => $templates['admin'],
			"admin_version" => $templates['admin'],
			"preview" => $templates['admin'],
			"show" => $templates['default'],
			"embed" => $templates['default'],
		)
	);