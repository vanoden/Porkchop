<?php
	$modules['Package'] = array(
		"schema"    =>2,
		"privileges"	=> array(
			"manage packages"
		),
		"templates"     => array(
			"packages"          => $templates['admin'],
			"package"           => $templates['admin'],
			"versions"          => $templates['admin'],
		),
	);