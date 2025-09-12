<?php
	$modules["Product"] = array(
		"schema"	=> 16,
		"privileges"	=> array(
			"edit product prices",
			"manage products",
			"manage product instances",
			"add product instances",
			"see product api"
        ),
		"templates"		=> array(
			"report"					=> $templates['admin'],
			"edit"						=> $templates['admin'],
			"admin_images"				=> $templates['admin'],
			"admin_product_prices"		=> $templates['admin'],
			"admin_vendors"				=> $templates['admin'],
			"admin_product_vendors"		=> $templates['admin'],
			"admin_product_tags"		=> $templates['admin'],
			"admin_product_parts"		=> $templates['admin'],
			"audit_log"					=> $templates['admin'],
			"admin_product_metadata"		=> $templates['admin']
		)
	);
