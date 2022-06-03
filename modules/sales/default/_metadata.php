<?php
	$modules['Sales'] = array(
        "schema"    => 7,
        "privileges"    => array(
            "browse sales orders",
            "approve sales order",
            "edit sales order",
            "edit currencies"
        ),
        "roles"         => array(
            "sales manager" => array(),
        ),
    );