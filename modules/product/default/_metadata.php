<?php
	$modules["Product"] = array(
		"schema"	=> 9,
		"privileges"	=> array(
			"edit product prices",
			"manage products",
			"manage product instances",
			"add product instances",
			"see product api"
        ),
		"templates"		=> array(
			"report"			=> $templates['admin'],
			"edit"				=> $templates['admin'],
		)
	);
