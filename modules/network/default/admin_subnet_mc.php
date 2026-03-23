<?php
	/** @view /_network/admin_subnet
	 * @description View for managing a single network subnet in the admin interface.
	 * @privilege manage subnets
	 */
	$porkchop = new \Porkchop();
	$site = $porkchop->site();
	$page = $site->page();
	$page->requirePrivilege("manage subnets");

	// Get Subnet ID from URL
	if (!empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
		$subnet_id = (int)$_REQUEST['id'];
	}
	elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0]) && is_numeric($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$subnet_id = (int)$GLOBALS['_REQUEST_']->query_vars_array[0];
	}
	else {
		$page->addError("Invalid Subnet ID");
		return;
	}

	// Get Subnet
	$subnet = new \Network\Subnet($subnet_id);
	if ($subnet->error()) {
		$page->addError("Subnet not found");
		return;
	}

	// Get Session
	$session = $subnet->session();
	if ($session && $session->id) {
		$hits = $session->hits();
	}

	require_once THIRD_PARTY . "/autoload.php";
	use Iodev\Whois\Factory as WhoisFactory;
	try {
		$whois = WhoisFactory::get()->createWhois();
		$whois_info = $whois->lookupDomain($subnet->realAddress());
		print_r($whois_info);
	} catch (Exception $e) {
		error_log("Error performing WHOIS lookup: " . $e->getMessage());
		$whois_info = null;
	}