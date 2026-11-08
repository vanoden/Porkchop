<?php
	/** @view /_register/partners
	 * Partnerships where the current organization shares data (organization_id),
	 * plus partnerships where the current organization is the partner receiving access.
	 */
	$page = new \Site\Page();
	$page->requireOrganization();

	$customer = $GLOBALS['_SESSION_']->customer();
	$organization = $customer->organization();
	$can_manage = $customer->can('manage customers', \Register\PrivilegeLevel::ORGANIZATION_MANAGER);

	$entityList = new \Register\Partner\EntityList();

	if ($can_manage && !empty($_REQUEST['method']) && $_REQUEST['method'] === 'Revoke' && !empty($_REQUEST['partnership_id'])) {
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
			$page->addError("Invalid Request");
		}
		else {
			$entity = new \Register\Partner\Entity((int)$_REQUEST['partnership_id']);
			if (!$entity->exists() || (int)$entity->organization_id !== (int)$organization->id) {
				$page->addError("Partnership not found");
			}
			elseif ($entity->delete()) {
				$page->appendSuccess("Partnership revoked. All implicit and explicit shares for that partner were removed.");
			}
			else {
				$page->addError($entity->error() ?: "Unable to revoke partnership");
			}
		}
	}

	$sharing = $entityList->find(array('organization_id' => $organization->id));
	if ($entityList->error()) {
		$page->addError($entityList->error());
		$sharing = array();
	}

	$receiving = $entityList->find(array('partner_id' => $organization->id));
	if ($entityList->error()) {
		$page->addError($entityList->error());
		$receiving = array();
	}
