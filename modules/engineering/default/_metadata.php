<?php
	$modules["Engineering"] = array(
		"schema"	=> 14,
		"templates"	=> array(
			"home"              => $templates['admin'],
			"tasks"             => $templates['admin'],
			"task"              => $templates['admin'],
			"releases"          => $templates['admin'],
			"release"           => $templates['admin'],
			"products"          => $templates['admin'],
			"product"           => $templates['admin'],
			"projects"          => $templates['admin'],
			"project"           => $templates['admin'],
			"event_report"      => $templates['admin'],
			"search"            => $templates['admin'],
		),
		"privileges"	=> array(
			"manage engineering events",
			"manage engineering module",
			"manage engineering tasks",
			"use engineering module",
		)
	);
