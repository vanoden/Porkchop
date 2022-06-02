<?php
	$modules['Support'] = array(
        "schema"    => 8,
        "privileges"    => array(
            "browse support tickets",
            "manage support requests"
        ),
        "templates"     => array(
            "request_new"           => $templates['admin'],
            "request_new_monitor"   => $templates['admin'],
            "requests"              => $templates['admin'],
            "request_detail"        => $templates['admin'],
            "request_items"         => $templates['admin'],
            "request_item"          => $templates['admin'],
            "action"                => $templates['admin'],
            "admin_actions"         => $templates['admin'],
            "pending_registrations" => $templates['admin'],
            "admin_rmas"            => $templates['admin'],
            "admin_rma"             => $templates['admin'],
            "summary"               => $templates['admin'],
        ),
    );