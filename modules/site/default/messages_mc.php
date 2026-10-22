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
	$inboxRows = array();
	$readRows = array();

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
		$fromName = $siteMessage->senderLabel();
		$senderName = ($fromName !== '') ? $fromName : $sender->full_name();
		$nameParts = preg_split('/\s+/', trim($senderName));
		if (count($nameParts) >= 2) {
			$senderInitials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
		} elseif (!empty($nameParts[0])) {
			$senderInitials = strtoupper(substr($nameParts[0], 0, 2));
		} else {
			$senderInitials = '?';
		}

		$plainContent = siteMessageReadableText((string) $siteMessage->content);
		$preview = $plainContent;
		if (strlen($preview) > 100) {
			$preview = substr($preview, 0, 100) . '...';
		}

		$messageTimestamp = strtotime($siteMessage->date_created);
		$listTime = (date('Y-m-d', $messageTimestamp) === date('Y-m-d'))
			? date('G:i', $messageTimestamp)
			: date('M j', $messageTimestamp);

		$row = array(
			'message' => $siteMessage,
			'delivery' => $delivery,
			'senderName' => $senderName,
			'senderInitials' => $senderInitials,
			'isRead' => $isRead,
			'plainContent' => $plainContent,
			'isHtml' => siteMessageIsHtml((string) $siteMessage->content),
			'frameHtml' => siteMessageFrameHtml((string) $siteMessage->content),
			'preview' => $preview,
			'listTime' => $listTime,
		);
		$messageRows[] = $row;
		if ($isRead) $readRows[] = $row;
		else $inboxRows[] = $row;
	}

function siteMessageReadableText(string $content): string {
	$text = preg_replace('#<(script|style|head)\b[^>]*>.*?</\1>#is', ' ', $content);
	$text = preg_replace('/<!--.*?-->/s', ' ', (string) $text);
	$text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$text = preg_replace('/\s+/u', ' ', (string) $text);
	return trim((string) $text);
}

function siteMessageIsHtml(string $content): bool {
	return (bool) preg_match('/<(?:html|body|table|div|p|br|style)\b/i', $content);
}

function siteMessageFrameHtml(string $content): string {
	$base = '<base target="_blank">';
	if (preg_match('/<head\b[^>]*>/i', $content)) {
		return preg_replace('/<head\b[^>]*>/i', '$0' . $base, $content, 1);
	}
	return $base . $content;
}
