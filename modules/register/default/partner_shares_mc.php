<?php
	/** @view /_register/partner_shares
	 * Manage implicit and explicit shares for a partnership owned by the current organization.
	 */
	$page = new \Site\Page();
	$page->requireOrganization();

	$customer = $GLOBALS['_SESSION_']->customer();
	$organization = $customer->organization();
	$can_manage = $customer->can('manage customers', \Register\PrivilegeLevel::ORGANIZATION_MANAGER);

	$partnership_id = (int)($_REQUEST['partnership_id'] ?? 0);
	$entity = new \Register\Partner\Entity($partnership_id);
	if (!$entity->exists() || (int)$entity->organization_id !== (int)$organization->id) {
		$page->addError("Partnership not found");
		$entity = null;
	}

	if ($entity && $can_manage && !empty($_REQUEST['method'])) {
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
			$page->addError("Invalid Request");
		}
		elseif ($_REQUEST['method'] === 'Add Implicit Share') {
			$share = new \Register\Partner\ImplicitShare();
			if ($share->addShare(array(
				'partnership_id' => $entity->id,
				'resource_class' => $_REQUEST['resource_class'] ?? '',
				'filters' => $_REQUEST['filters'] ?? '{}',
				'read_only' => !empty($_REQUEST['read_only']) ? 1 : 0
			))) {
				$page->appendSuccess("Implicit share added");
			}
			else {
				$page->addError($share->error() ?: "Unable to add implicit share");
			}
		}
		elseif ($_REQUEST['method'] === 'Add Explicit Share') {
			$share = new \Register\Partner\ExplicitShare();
			if ($share->addShare(array(
				'partnership_id' => $entity->id,
				'resource_class' => $_REQUEST['resource_class'] ?? '',
				'resource_id' => $_REQUEST['resource_id'] ?? null,
				'read_only' => !empty($_REQUEST['read_only']) ? 1 : 0
			))) {
				$page->appendSuccess("Explicit share added");
			}
			else {
				$page->addError($share->error() ?: "Unable to add explicit share");
			}
		}
		elseif ($_REQUEST['method'] === 'Delete Implicit' && !empty($_REQUEST['share_id'])) {
			$share = new \Register\Partner\ImplicitShare((int)$_REQUEST['share_id']);
			if ($share->exists() && (int)$share->partnership_id === (int)$entity->id && $share->delete()) {
				$page->appendSuccess("Implicit share removed");
			}
			else {
				$page->addError($share->error() ?: "Unable to remove implicit share");
			}
		}
		elseif ($_REQUEST['method'] === 'Delete Explicit' && !empty($_REQUEST['share_id'])) {
			$share = new \Register\Partner\ExplicitShare((int)$_REQUEST['share_id']);
			if ($share->exists() && (int)$share->partnership_id === (int)$entity->id && $share->delete()) {
				$page->appendSuccess("Explicit share removed");
			}
			else {
				$page->addError($share->error() ?: "Unable to remove explicit share");
			}
		}
	}

	$implicit = array();
	$explicit = array();
	$partner = null;
	if ($entity) {
		$partner = $entity->partner();
		$implicitList = new \Register\Partner\ImplicitShareList();
		$implicit = $implicitList->find(array('partnership_id' => $entity->id));
		if ($implicitList->error()) {
			$page->addError($implicitList->error());
			$implicit = array();
		}
		$explicitList = new \Register\Partner\ExplicitShareList();
		$explicit = $explicitList->find(array('partnership_id' => $entity->id));
		if ($explicitList->error()) {
			$page->addError($explicitList->error());
			$explicit = array();
		}
	}
