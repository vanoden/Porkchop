<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');
	$request = new \Support\Request($_REQUEST['request_id']);

	if (isset($_REQUEST['btn_submit'])) {
		$requestor = new \Register\Customer($_REQUEST['requestor_id']);
		if (! $requestor->id) {
			$page->addError("Requestor not found");
		} else {
			$request->addAction(
				array(
					'date_action' 	=> $_REQUEST['date_action'],
					'requestor_id'	=> $_REQUEST['requestor_id'],
					'assigned_id'	=> $_REQUEST['assigned_id'],
					'status'		=> 'NEW',
					'type'			=> $_REQUEST['type'],
					'description'	=> $_REQUEST['description']
				)
			);
			if ($request->error()) {
				$page->addError($request->error());
			}
			elseif ($request->id) {
				header('location: /_support/request_detail/'.$request->code);
				exit;
			}
		}
	}
	
	if (! isset($_REQUEST['btn_submit'])) {
		$_REQUEST['requestor_id'] = $GLOBALS['_SESSION_']->customer->id;
		$_REQUEST['date_action'] = date('m/d H:i');
	}
	$userlist = new \Register\CustomerList();
	$users = $userlist->find();
