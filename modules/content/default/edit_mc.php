<?php
	if (isset($_REQUEST['id'])) {
		$message = new \Content\Message($_REQUEST['id']);
	}
	else {
		$message = new \Content\Message($GLOBALS['_REQUEST_']->index);
		if (! $message->id) {
			$message->name = $GLOBALS['_REQUEST_']->index;
			$message->target = $GLOBALS['_REQUEST_']->index;
		}
	}