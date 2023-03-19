<?php
	$modules["Storage"] = array(
		"schema"	=> 6,
		"privileges"	=> array(
			"manage storage files"
		),
		"templates"	=> array(
			"browse"	=> $templates['admin'],
			"repositories"      => $templates['admin'],
			"repository"        => $templates['admin']
		)
	);