<?php
	/** @view /_network/admin_subnets
	 * @description View for managing network subnets in the admin interface.
	 * @privilege manage subnets
	 */
	$porkchop = new \Porkchop();
	$site = $porkchop->site();
	$page = $site->page();
	$page->requirePrivilege("manage subnets");

	// Get List if recorded subnets
	$subnet_list = new \Network\SubnetList();
	$subnets = $subnet_list->find(null, array('date_last_seen' => 'DESC'));

	// Page Heading
	$page->title("Recorded Subnets");
	$page->addBreadcrumb("Network");
	$page->addBreadcrumb("Subnets", "/network/admin_subnets");