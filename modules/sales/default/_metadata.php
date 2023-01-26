<?php
	$modules['Sales'] = array(
        "schema"    => 8,
        "privileges"    => array(
            "browse sales orders",
            "approve sales order",
            "edit sales order",
            "edit currencies"
        ),
        "roles"         => array(
            "sales manager" => array(),
        ),
		"templates"		=> array(
			"cart"		=> $templates['admin'],
		),        
    );
