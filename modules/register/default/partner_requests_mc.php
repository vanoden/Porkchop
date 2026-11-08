<?php
	/** @view /_register/partner_requests
	 * Create and manage outgoing partnership access requests.
	 * Creating a request asks another organization for access; it does not share data.
	 */
	$page = new \Site\Page();
	$page->requireOrganization();

	$customer = $GLOBALS['_SESSION_']->customer();
	$organization = $customer->organization();
	$can_manage = $customer->can('manage customers', \Register\PrivilegeLevel::ORGANIZATION_MANAGER);

	$requestModel = new \Register\Partner\Request();
	$requestModel->expireStale($organization->id);
	$active_count = $requestModel->countActive($organization->id);

	if ($can_manage && !empty($_REQUEST['method'])) {
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
			$page->addError("Invalid Request");
		}
		elseif ($_REQUEST['method'] === 'Create Request') {
			$request = new \Register\Partner\Request();
			if ($request->createRequest(array('email' => $_REQUEST['email'] ?? ''))) {
				if ($request->email_delivered) {
					$page->appendSuccess("Partnership access request created and emailed. Approving the request will not share data; the other organization must still choose what to share.");
				}
				else {
					$page->appendSuccess("Partnership access request created. No matching user was found for that email, so no message was sent.");
				}
				$active_count = $requestModel->countActive($organization->id);
			}
			else {
				$page->addError($request->error() ?: "Unable to create partnership request");
			}
		}
		elseif ($_REQUEST['method'] === 'Cancel' && !empty($_REQUEST['request_id'])) {
			$request = new \Register\Partner\Request((int)$_REQUEST['request_id']);
			if (!$request->exists() || (int)$request->requesting_organization_id !== (int)$organization->id) {
				$page->addError("Partnership request not found");
			}
			elseif ($request->cancel()) {
				$page->appendSuccess("Partnership request cancelled");
				$active_count = $requestModel->countActive($organization->id);
			}
			else {
				$page->addError($request->error() ?: "Unable to cancel request");
			}
		}
	}

	$list = new \Register\Partner\RequestList();
	$requests = $list->find(array('requesting_organization_id' => $organization->id));
	if ($list->error()) {
		$page->addError($list->error());
		$requests = array();
	}
