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

		fileMessageAsRead(listItem);

		if (typeof refreshUnreadBadge === 'function') {
			refreshUnreadBadge();
		}
	}

	function showMessageFolder(folder) {
		var inboxList = document.getElementById('site-messages-inbox');
		var readList = document.getElementById('site-messages-read');
		var inboxTab = document.getElementById('site-messages-folder-inbox');
		var readTab = document.getElementById('site-messages-folder-read');
		var showRead = folder === 'read';

		inboxList.classList.toggle('is-hidden', showRead);
		readList.classList.toggle('is-hidden', !showRead);
		inboxTab.classList.toggle('is-active', !showRead);
		readTab.classList.toggle('is-active', showRead);
		inboxTab.setAttribute('aria-selected', showRead ? 'false' : 'true');
		readTab.setAttribute('aria-selected', showRead ? 'true' : 'false');
	}

	function fileMessageAsRead(listItem) {
		if (!listItem) return;
		var readList = document.getElementById('site-messages-read');
		listItem.classList.remove('message-unread');
		listItem.classList.add('message-read');
		if (listItem.parentElement !== readList) {
			readList.insertBefore(listItem, readList.firstChild);
		}
		updateMessageFolderChrome();
	}

	function updateMessageFolderChrome() {
		var inboxCount = document.querySelectorAll('#site-messages-inbox .site-messages__item').length;
		var readCount = document.querySelectorAll('#site-messages-read .site-messages__item').length;
		document.getElementById('site-messages-inbox-count').textContent = String(inboxCount);
		document.getElementById('site-messages-read-count').textContent = String(readCount);
		document.getElementById('site-messages-inbox-empty').classList.toggle('is-hidden', inboxCount > 0);
		document.getElementById('site-messages-read-empty').classList.toggle('is-hidden', readCount > 0);
	}
</script>

<?= $page->showBreadCrumbs() ?>
<?= $page->showTitle() ?>
<?= $page->showMessages() ?>

<?php
if (!function_exists('siteMessageListItem')) {
	function siteMessageListItem(array $row): void {
		$siteMessage = $row['message'];
		$messageId = (int) $siteMessage->id;
		$rowClass = $row['isRead'] ? 'message-read' : 'message-unread';
		$avatarClass = 'site-messages__avatar--' . ($messageId % 6 + 1);
?>
				<li id="row-<?= $messageId ?>" class="site-messages__item <?= $rowClass ?>" data-message-id="<?= $messageId ?>" onclick="selectMessage(<?= $messageId ?>)">
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
}
?>
<section class="site-messages">
	<div class="site-messages__client">
		<div class="site-messages__list-pane">
			<div class="site-messages__list-toolbar">
				<div class="site-messages__folders" role="tablist" aria-label="Message folders">
					<button type="button" class="site-messages__folder is-active" id="site-messages-folder-inbox" role="tab" aria-selected="true" aria-controls="site-messages-inbox" onclick="showMessageFolder('inbox')">
						Inbox <span class="site-messages__folder-count" id="site-messages-inbox-count"><?= count($inboxRows) ?></span>
					</button>
					<button type="button" class="site-messages__folder" id="site-messages-folder-read" role="tab" aria-selected="false" aria-controls="site-messages-read" onclick="showMessageFolder('read')">
						Read <span class="site-messages__folder-count" id="site-messages-read-count"><?= count($readRows) ?></span>
					</button>
				</div>
			</div>

			<ul class="site-messages__list" id="site-messages-inbox" role="list">
				<li id="site-messages-inbox-empty" class="site-messages__empty-list<?= empty($inboxRows) ? '' : ' is-hidden' ?>">No unread messages.</li>
<?php foreach ($inboxRows as $row) siteMessageListItem($row); ?>
			</ul>
			<ul class="site-messages__list is-hidden" id="site-messages-read" role="list">
				<li id="site-messages-read-empty" class="site-messages__empty-list<?= empty($readRows) ? '' : ' is-hidden' ?>">No read messages.</li>
<?php foreach ($readRows as $row) siteMessageListItem($row); ?>
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
?>
			<article id="reading-<?= $messageId ?>" class="site-messages__reading">
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
				<?php if (!empty($row['isHtml'])) { ?>
				<iframe class="site-messages__reading-frame" title="<?= htmlspecialchars($siteMessage->subject) ?>" sandbox="allow-popups allow-popups-to-escape-sandbox" referrerpolicy="no-referrer" srcdoc="<?= htmlspecialchars($row['frameHtml'], ENT_QUOTES, 'UTF-8') ?>"></iframe>
				<?php } else { ?>
				<div class="site-messages__reading-body"><?= nl2br(htmlspecialchars($row['plainContent'])) ?></div>
				<?php } ?>
			</article>
<?php } ?>
		</div>
	</div>
</section>
