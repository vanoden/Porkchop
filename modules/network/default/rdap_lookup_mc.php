<?php
	/** @view /_network/rdap_lookup
	 * @description View for performing RDAP lookup for a given subnet.
	 * @privilege manage subnets
	 */
	$porkchop = new \Porkchop();
	$site = $porkchop->site();
	$page = $site->page();
	$page->requirePrivilege("manage network");

	// Get Address and Type from URL
	if (!empty($_REQUEST['address']) && !empty($_REQUEST['type'])) {
		$address = $_REQUEST['address'];
		$type = $_REQUEST['type'];
	}
	elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0]) && !empty($GLOBALS['_REQUEST_']->query_vars_array[1])) {
		$address = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$type = $GLOBALS['_REQUEST_']->query_vars_array[1];
	}
	else {
		$page->addError("Invalid Address or Type");
		return;
	}

	// Perform RDAP Lookup
	$rdap = new \Network\RDAP();
	$result = $rdap->lookup($address, $type);
	if ($result === false) {
		$page->addError("RDAP lookup failed");
		return;
	}