<?php
	$page = new \Site\Page();
	$page->fromRequest();
    $page->requirePrivilege('manage support requests');

	$request = new \Support\Request($_REQUEST['request_id']);

	if (isset($_REQUEST['btn_submit'])) {
		$request->addNote(
			array(
				'date_note' => date('Y-m-d H:i:s'),
				'author_id'	=> $GLOBALS['_SESSION_']->customer->id,
				'description'	=> $_REQUEST['note']
			)
		);
		if ($request->error()) {
			$page->addError($request->error());
		}
		else {
			header('location: /_support/request_detail/'.$request->code);
			exit;
		}
	}
