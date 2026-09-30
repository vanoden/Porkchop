<script>
	var siteMessagesCsrfToken = '<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>';
	var siteMessagesSelectedId = null;

	function siteApiRequest(params) {
		var resultXML = apiRequest('/_site/api', params);
		if (!resultXML) {
			return { ok: false, error: 'No response from server' };
		}

		var resultObj = XMLParse.xmlElem2Obj(resultXML);
		if (resultObj.success == 1) {
			return { ok: true };
		}

		return { ok: false, error: resultObj.error || 'Request failed' };
	}

	function selectMessage(messageId) {
		var listItem = document.getElementById('row-' + messageId);
		var readingPanel = document.getElementById('reading-' + messageId);
		if (!listItem || !readingPanel) return;

		document.querySelectorAll('.site-messages__item.is-selected').forEach(function(item) {
			item.classList.remove('is-selected');
		});
		document.querySelectorAll('.site-messages__reading.is-active').forEach(function(panel) {
			panel.classList.remove('is-active');
		});

		listItem.classList.add('is-selected');
		readingPanel.classList.add('is-active');
		siteMessagesSelectedId = messageId;

		if (listItem.classList.contains('message-unread')) {
			acknowledge(messageId);
		}

		var client = document.querySelector('.site-messages__client');
		if (client && window.matchMedia('(max-width: 900px)').matches) {
			readingPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}
	}

	function acknowledge(messageId) {
		var params = 'method=acknowledgeSiteMessage&csrfToken=' + encodeURIComponent(siteMessagesCsrfToken) + '&message_id=' + encodeURIComponent(messageId);
		var result = siteApiRequest(params);
		if (!result.ok) {
			console.error('Error acknowledging message:', result.error);
			return;
		}

		var listItem = document.getElementById('row-' + messageId);
		if (!listItem) return;

		listItem.classList.remove('message-unread');
		listItem.classList.add('message-read');

		if (typeof refreshUnreadBadge === 'function') {
			refreshUnreadBadge();
		}
	}

	document.addEventListener('DOMContentLoaded', function() {
		var firstItem = document.querySelector('.site-messages__item');
		if (firstItem) {
			selectMessage(parseInt(firstItem.getAttribute('data-message-id'), 10));
		}
	});
</script>

<?= $page->showBreadCrumbs() ?>
<?= $page->showTitle() ?>
<?= $page->showMessages() ?>

<section class="site-messages">
	<div class="site-messages__client">
		<div class="site-messages__list-pane">
			<div class="site-messages__list-toolbar">
				<h2 class="site-messages__list-title">Inbox</h2>
			</div>

			<ul class="site-messages__list" role="list">
<?php if (empty($messageRows)) { ?>
				<li class="site-messages__empty-list">No messages to display.</li>
<?php } else {
	foreach ($messageRows as $row) {
		$siteMessage = $row['message'];
		$messageId = (int) $siteMessage->id;
		$rowClass = $row['isRead'] ? 'message-read' : 'message-unread';
		$avatarClass = 'site-messages__avatar--' . ((int) $messageId % 6 + 1);
?>
				<li id="row-<?= $messageId ?>" class="site-messages__item <?= $rowClass ?><?= ($messageId === $firstMessageId) ? ' is-selected' : '' ?>" data-message-id="<?= $messageId ?>" onclick="selectMessage(<?= $messageId ?>)">
					<div class="site-messages__avatar <?= $avatarClass ?>" aria-hidden="true"><?= htmlspecialchars($row['senderInitials']) ?></div>
					<div class="site-messages__summary">
						<div class="site-messages__summary-top">
							<span class="site-messages__sender"><?= htmlspecialchars($row['senderName']) ?></span>
							<time class="site-messages__time" datetime="<?= htmlspecialchars(date('c', strtotime($siteMessage->date_created))) ?>"><?= htmlspecialchars($row['listTime']) ?></time>
						</div>
						<div class="site-messages__snippet">
							<span class="site-messages__snippet-subject"><?= htmlspecialchars($siteMessage->subject) ?></span>
							<?php if ($row['preview'] !== '') { ?>
							<span class="site-messages__snippet-preview"> — <?= htmlspecialchars($row['preview']) ?></span>
							<?php } ?>
						</div>
					</div>
				</li>
<?php
	}
} ?>
			</ul>
		</div>

		<div class="site-messages__reading-pane" id="site-messages-reading-pane">
			<div class="site-messages__reading-placeholder" id="site-messages-reading-placeholder">
				<?php if (empty($messageRows)) { ?>
					<p>No messages in this folder.</p>
				<?php } else { ?>
					<p>Select a message to read it.</p>
				<?php } ?>
			</div>

<?php foreach ($messageRows as $row) {
	$siteMessage = $row['message'];
	$messageId = (int) $siteMessage->id;
	$avatarClass = 'site-messages__avatar--' . ((int) $messageId % 6 + 1);
	$isActive = ($messageId === $firstMessageId);
?>
			<article id="reading-<?= $messageId ?>" class="site-messages__reading<?= $isActive ? ' is-active' : '' ?>">
				<header class="site-messages__reading-header">
					<h3 class="site-messages__reading-subject"><?= htmlspecialchars($siteMessage->subject) ?></h3>
					<div class="site-messages__reading-from">
						<div class="site-messages__reading-from-row">
							<div class="site-messages__avatar <?= $avatarClass ?>" aria-hidden="true"><?= htmlspecialchars($row['senderInitials']) ?></div>
							<div class="site-messages__reading-from-meta">
								<strong><?= htmlspecialchars($row['senderName']) ?></strong>
								<span><?= htmlspecialchars(date('M j, Y g:i A', strtotime($siteMessage->date_created))) ?></span>
							</div>
						</div>
					</div>
				</header>
				<div class="site-messages__reading-body"><?= nl2br(htmlspecialchars($row['plainContent'])) ?></div>
			</article>
<?php } ?>
		</div>
	</div>
</section>
