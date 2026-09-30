<?php
	/** @view /_register/inventory_report
	 * Organization Inventory Report: count of active devices by product
	 * for a selected company.
	 */
	$page = new \Site\Page();
	$page->requirePrivilege('manage customers');

	$can_proceed = true;
	$run_search = !empty($_REQUEST['btn_submit']) || !empty($_REQUEST['organization_id']);
	$search_ran = false;
	$inventory_rows = array();
	$device_total = 0;
	$selected_organization = new \Register\Organization();

	if (!empty($_REQUEST['organization_id'])) {
		if (!$selected_organization->validInteger($_REQUEST['organization_id'])) {
			$page->addError("Invalid organization ID format");
			$can_proceed = false;
		}
		else {
			$selected_organization = new \Register\Organization($_REQUEST['organization_id']);
			if ($selected_organization->error()) {
				$page->addError("Unable to load organization: ".$selected_organization->error());
				$can_proceed = false;
			}
			elseif (!$selected_organization->exists()) {
				$page->addError("Organization not found");
				$can_proceed = false;
			}
		}
	}

	if ($run_search && $can_proceed && empty($selected_organization->id)) {
		$page->addError("Select a company before searching");
		$can_proceed = false;
	}

	if ($run_search && $can_proceed) {
		$search_ran = true;
		$inventory_rows = $selected_organization->activeDevicesByProduct();
		if ($selected_organization->error()) {
			$page->addError("Error loading inventory: ".$selected_organization->error());
			$inventory_rows = array();
		}
		else {
			foreach ($inventory_rows as $row) {
				$device_total += $row->count;
			}
		}
	}

	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find(array('status' => array('NEW','ACTIVE')));
	if ($organizationlist->error()) {
		$page->addError("Error getting organizations for selection: ".$organizationlist->error());
		$organizations = array();
	}

	$page->title("Organization Inventory Report");
	$page->setAdminMenuSection("Customer");
	$page->instructions = "Select a company to list, by product, the number of active devices it owns.";
	$page->addBreadcrumb("Customer");
	$page->addBreadcrumb("Organizations", "/_register/admin_organizations");
	$page->addBreadcrumb("Inventory Report", "/_register/inventory_report");
