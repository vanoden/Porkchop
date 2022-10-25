<?php
	global $_config;
	$page = new \Site\Page();

	if ($_REQUEST['item_id']) {
		$item = new \Support\Request\Item($_REQUEST['item_id']);
	} elseif ($_REQUEST['request_id'] && $_REQUEST['line']) {
		$item = new \Support\Request\Item();
		$item->get($_REQUEST['request_id'],$_REQUEST['line']);
	} elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$item = new \Support\Request\Item($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}
	$request = $item->request;
    if (! $item->id) {
        return 404;
    }
	if ($request->customer->organization->id != $GLOBALS['_SESSION_']->customer->organization->id && !$GLOBALS['_SESSION_']->customer->can('browse support tickets')) {
        return 403;
	}

	if ($_REQUEST['btn_reopen_item']) $item->update(array('status' => 'ACTIVE'));
	    elseif ($_REQUEST['btn_close_item']) $item->update(array('status' => 'CLOSED'));

	if ($_REQUEST['btn_add_comment']) {
		$parameters = array(
			'author_id'	=> $GLOBALS['_SESSION_']->customer->id,
			'content'	=> $_REQUEST['content'],
			'status'	=> $_REQUEST['action_status']
		);
		$item->addComment($parameters);
		if ($item->error()) $page->addError("Unable to add comment: ".$item->error());
	}

	$rmalist = new \Support\Request\Item\RMAList();
	$rmas = $rmalist->find(array('item_id' => $item->id));
	if ($rmalist->error()) $page->addError($rmalist->error());

	$commentlist = new \Support\Request\Item\CommentList();
	$comments = $commentlist->find(array('item_id' => $item->id));
	if ($commentlist->error()) $page->addError($commentlist->error());

	$actionlist = new \Support\Request\Item\ActionList();
	$actions = $actionlist->find(array('item_id' => $item->id));
	if ($actionlist->error()) $page->addError($actionlist->error());

	$filesList = new \Storage\FileList();
	$filesUploaded = $filesList->find(array('type' => 'support ticket', 'ref_id' => $item->id));

