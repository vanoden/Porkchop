<?php
	$page = new \Site\Page();
	$page->requireAuth();
	$page->requireOrganization();

	if (empty($GLOBALS['_SESSION_']->customer->organization()->id)) {
		$page->addError("Your registration has not been completed.  Please make sure you've validated your email and contact " . $GLOBALS['_config']->site->support_email . ' for assistance.');
	}

	$userId = (int) $GLOBALS['_SESSION_']->customer->id;

	$params = array(
		'recipient_id' => $userId,
	);

	$siteMessagesList = new \Site\SiteMessagesList();
	$siteMessages = $siteMessagesList->find($params);
	if ($siteMessagesList->error()) $page->addError($siteMessagesList->error());

	$messageRows = array();

	foreach ($siteMessages as $siteMessage) {
		$delivery = new \Site\SiteMessageDelivery();
		if (!$delivery->getDelivery($siteMessage->id, $userId)) {
			$delivery->add(array(
				'message_id' => $siteMessage->id,
				'user_id' => $userId,
				'date_viewed' => date('Y-m-d H:i:s'),
			));
			if ($delivery->error()) {
				$page->addError($delivery->error());
				continue;
			}
			$delivery->getDelivery($siteMessage->id, $userId);
		}

		$isRead = $delivery->acknowledged();

		$sender = new \Register\Customer($siteMessage->user_created);
		$senderName = $sender->full_name();
		$nameParts = preg_split('/\s+/', trim($senderName));
		if (count($nameParts) >= 2) {
			$senderInitials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
		} elseif (!empty($nameParts[0])) {
			$senderInitials = strtoupper(substr($nameParts[0], 0, 2));
		} else {
			$senderInitials = '?';
		}

		$plainContent = strip_tags($siteMessage->content);
		$preview = $plainContent;
		if (strlen($preview) > 100) {
			$preview = substr($preview, 0, 100) . '...';
		}

		$messageTimestamp = strtotime($siteMessage->date_created);
		$listTime = (date('Y-m-d', $messageTimestamp) === date('Y-m-d'))
			? date('G:i', $messageTimestamp)
			: date('M j', $messageTimestamp);

		$messageRows[] = array(
			'message' => $siteMessage,
			'delivery' => $delivery,
			'senderName' => $senderName,
			'senderInitials' => $senderInitials,
			'isRead' => $isRead,
			'plainContent' => $plainContent,
			'preview' => $preview,
			'listTime' => $listTime,
		);
	}

	$firstMessageId = !empty($messageRows) ? (int) $messageRows[0]['message']->id : 0;
