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

	// Apply Changes
	if ($_POST['btn_save'] && $_POST['csrf_token'] && $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrf_token'])) {
		$parameters = [
			'address' => $_POST['subnet_address'],
			'size' => $_POST['subnet_size'],
			'type' => $_POST['subnet_type'],
			'risk_level' => $_POST['subnet_risk_level'],
			'managed' => $_POST['subnet_managed']
		];
		if ($subnet->id) {
			if ($subnet->update($parameters)) {
				$page->addMessage("Subnet updated successfully");
			}
			else {
				$page->addError("Failed to update subnet: " . $subnet->error());
			}
		}
		elseif ($subnet->add($parameters)) {
			$page->addMessage("Subnet added successfully");
		}
		else {
			$page->addError("Failed to add subnet: " . $subnet->error());
		}
	}

	// Get Session
	$session = $subnet->session();
	if ($session && $session->id) {
		$hits = $session->hits();
	}

	// Page Heading
	if ($subnet->id) {
		$page->title("Manage Subnet");
	}
	else {
		$page->title("Add New Subnet");
	}
	$page->addBreadcrumb("Network");
	$page->addBreadcrumb("Subnets", "/_network/admin_subnets");
	if ($subnet->id) {
		$page->addBreadcrumb($subnet->realAddress());
	}