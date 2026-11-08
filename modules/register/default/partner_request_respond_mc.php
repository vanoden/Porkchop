<?php
	/** @view /_register/partner_request_respond
	 * Approve or deny a partnership access request via emailed token.
	 * Approval creates partner_entities only; it does not share data.
	 */
	$page = new \Site\Page();
	$page->requireAuth();
	$page->requireOrganization();

	$token = trim($_REQUEST['token'] ?? '');
	$request = new \Register\Partner\Request();
	$ready = false;

	if ($request->getByToken($token)) {
		$request->expireStale($request->requesting_organization_id);
		$request->details();
		$ready = true;
	}
	else {
		$page->addError($request->error() ?: "Partnership request not found");
	}

	$customer = $GLOBALS['_SESSION_']->customer();
	$requester_org = $ready ? $request->requestingOrganization() : null;
	$requester = $ready ? $request->requester() : null;

	if ($ready && !empty($_REQUEST['method'])) {
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
			$page->addError("Invalid Request");
		}
		elseif ($_REQUEST['method'] === 'Approve') {
			if ($request->respond('APPROVE')) {
				$page->appendSuccess("Partnership approved. No data has been shared. You can now choose resources to share with ".$requester_org->name.".");
			}
			else {
				$page->addError($request->error() ?: "Unable to approve partnership request");
			}
		}
		elseif ($_REQUEST['method'] === 'Deny') {
			if ($request->respond('DENY')) {
				$page->appendSuccess("Partnership request denied. No partnership was created and no data was shared.");
			}
			else {
				$page->addError($request->error() ?: "Unable to deny partnership request");
			}
		}
		$request->details();
	}

	$can_respond = $ready
		&& $request->status === \Register\Partner\Request::STATUS_ACTIVE
		&& !empty($customer)
		&& !empty($customer->id)
		&& !empty($customer->organization_id);
