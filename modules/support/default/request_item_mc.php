<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');

	if ($_REQUEST['item_id']) {
		$item = new \Support\Request\Item($_REQUEST['item_id']);
	}
	elseif ($_REQUEST['request_id'] && $_REQUEST['line']) {
		$item = new \Support\Request\Item();
		$item->get($_REQUEST['request_id'],$_REQUEST['line']);
	}
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$item = new \Support\Request\Item($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}

	$request = $item->request;

	if ($_REQUEST['btn_complete']) {
		if ($item->openActions() > 0) {
			$page->addError("Item has open actions!");
		}
		else {
			$item->update(array('status' => 'COMPLETE'));
			if ($item->error()) $page->addError($item->error());
		}
	}
	if ($_REQUEST['btn_close_item']) {
		if ($item->openActions() > 0) {
			$page->addError("Item has open actions!");
		}
		else {
			$item->update(array('status' => 'CLOSED'));
			if ($item->error()) $page->addError($item->error());
		}
	}
	if ($_REQUEST['btn_reopen_item']) $item->update(array('status' => 'ACTIVE'));
	if ($_REQUEST['btn_add_action']) {
		$parameters = array(
			'type'				=> $_REQUEST['action_type'],
			'date_requested'	=> $_REQUEST['action_date_request'],
			'requested_id'		=> $_REQUEST['action_requested_by'],
			'assigned_id'		=> $_REQUEST['action_assigned_to'],
			'status'			=> $_REQUEST['action_status'],
			'description'		=> $_REQUEST['action_description']
		);

		$action = $item->addAction($parameters);
		if ($item->error()) {
			$page->addError($item->error());
		} else {
			$page->success = "Action #".$action->id." added";
		}
		if ($item->status == "NEW") $item->update(array('status' => 'ACTIVE'));
		$assigned_to = new \Register\Customer($_REQUEST['action_assigned_to']);
		if ($assigned_to->id) {
			$message = new \Email\Message(
				array(
					'from'	=> 'service@spectrosinstruments.com',
					'subject'	=> "[SUPPORT] Action #".$action->id." assigned to you",
					'body'		=> "The following action was assigned to you:
Request: ".$action->item->request->code."<br>
Item: ".$action->item->line."<br>
Type: ".$action->type."<br>
Product: ".$action->item->product->code."<br>
Serial: ".$action->item->serial_number."<br>
Description: ".$action->description
				)
			);
			$message->html(true);
			$assigned_to->notify($message);
		}
	}
	if ($_REQUEST['btn_add_rma']) {
		// We create an RMA record.  Shipment is created by customer when they ship, or admin when received
		$parameters = array(
			'approved_id'	=> $GLOBALS['_SESSION_']->customer->id,
			'date_approved'	=> date('Y-m-d H:i:s'),
			'item_id'		=> $item->id
		);
		$item->addRMA($parameters);
		if ($item->error()) {
			$page->addError("Unable to create RMA: ".$item->error());
		}
		$item->update(array('status' => 'PENDING CUSTOMER'));
	}
	if ($_REQUEST['btn_add_shipment']) {
		$shipment = new \Shipping\Shipment($_REQUEST['shipment_id']);
		if ($shipment->error()) {
			$page->addError($shipment->error());
		} elseif (! $shipment->id) {
			$page->addError("Shipment not found");
		} else {
			$parameters = array(
				'user_id'		=> $GLOBALS['_SESSION_']->customer->id,
				'shipment_id'	=> $shipment->id,
			);
			$item->addToShipment($parameters);
			if ($item->error()) {
				$page->addError("Unable to associate shipment: ".$item->error());
			} else {
				$page->success = "Item ready for pickup";
			}
		}
	}
	if ($_REQUEST['btn_add_comment']) {
		$parameters = array(
			'author_id'	=> $GLOBALS['_SESSION_']->customer->id,
			'content'	=> $_REQUEST['content'],
			'status'	=> $_REQUEST['action_status']
		);
		$item->addComment($parameters);
		if ($item->error()) $page->addError("Unable to add comment: ".$item->error());
	}
	if ($_REQUEST['btn_submit']) {
		$item->update(
			array(
				'serial_number'		=> $_REQUEST['serial_number'],
				'product_id'		=> $_REQUEST['product_id']
			)
		);
		if ($item->error()) $page->addError($item->error());
	}
	$adminlist = new \Register\CustomerList();
	$admins = $adminlist->find(array('role' => 'support user'));
	
	$actionlist = new \Support\Request\Item\ActionList();
	$actions = $actionlist->find(array('item_id' => $item->id));
	if ($actionlist->error()) $page->addError($actionlist->error());

	$productlist = new \Product\ItemList();
	$products = $productlist->find();

	$rmalist = new \Support\Request\Item\RMAList();
	$rmas = $rmalist->find(array('item_id' => $item->id));

	$commentlist = new \Support\Request\Item\CommentList();
	$comments = $commentlist->find(array('item_id' => $item->id));
	if ($commentlist->error()) $page->addError($commentlist->error());
