<?php
	$modules['Shipping'] = array(
        "schema"    => 3,
        "privileges"    => array(
            "receive shipments",
			"manage shipments"
        ),
        "templates"     => array(
            "admin_shipments"           => $templates['admin'],
            "admin_shipment"            => $templates['admin'],
        ),
    );