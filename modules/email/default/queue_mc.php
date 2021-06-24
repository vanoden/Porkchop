<?php
	$page = new \Site\Page();
	$page->requireRole('email manager');

	$queue = new \Email\Queue();
	$messages = $queue->messages();
