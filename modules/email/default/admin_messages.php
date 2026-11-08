<?=$page->showAdminPageInfo()?>

<p>
	<a href="/_email/admin_messages">Messages</a>
	&nbsp;|&nbsp;
	<a href="/_email/admin_deliveries">Deliveries</a>
	&nbsp;|&nbsp;
	<a href="/_email/admin_attempts">Attempts</a>
</p>

<form method="get" class="monitor-admin-list">
	<div class="filter-bar">
		<div class="filter-bar__controls">
			<div class="form-field">
				<label for="user_code">User</label>
				<input type="text" id="user_code" name="user_code" value="<?=htmlspecialchars((string)($_REQUEST['user_code'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
			<div class="form-field">
				<label for="from_address">From</label>
				<input type="text" id="from_address" name="from_address" value="<?=htmlspecialchars((string)($_REQUEST['from_address'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
			<div class="form-field">
				<label for="mime_type">MIME type</label>
				<input type="text" id="mime_type" name="mime_type" value="<?=htmlspecialchars((string)($_REQUEST['mime_type'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
			<div class="form-field">
				<label for="date_queued_start">Queued after</label>
				<input type="text" id="date_queued_start" name="date_queued_start" placeholder="YYYY-MM-DD HH:MM:SS" value="<?=htmlspecialchars((string)($_REQUEST['date_queued_start'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
			<div class="form-field">
				<label for="date_queued_end">Queued before</label>
				<input type="text" id="date_queued_end" name="date_queued_end" placeholder="YYYY-MM-DD HH:MM:SS" value="<?=htmlspecialchars((string)($_REQUEST['date_queued_end'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
			<div class="form-field">
				<label for="date_attempt_start">Attempt after</label>
				<input type="text" id="date_attempt_start" name="date_attempt_start" placeholder="YYYY-MM-DD HH:MM:SS" value="<?=htmlspecialchars((string)($_REQUEST['date_attempt_start'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
			<div class="form-field">
				<label for="date_attempt_end">Attempt before</label>
				<input type="text" id="date_attempt_end" name="date_attempt_end" placeholder="YYYY-MM-DD HH:MM:SS" value="<?=htmlspecialchars((string)($_REQUEST['date_attempt_end'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
			<div class="form-field form-field--narrow">
				<label for="count_attempt_more">Attempts at least</label>
				<input type="text" id="count_attempt_more" name="count_attempt_more" value="<?=htmlspecialchars((string)($_REQUEST['count_attempt_more'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
			<div class="form-field form-field--narrow">
				<label for="count_attempt_less">Attempts at most</label>
				<input type="text" id="count_attempt_less" name="count_attempt_less" value="<?=htmlspecialchars((string)($_REQUEST['count_attempt_less'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
		</div>
		<div class="button-group filter-bar__actions">
			<button type="submit">Search</button>
			<a class="button btn-secondary" href="/_email/admin_messages">Clear</a>
		</div>
	</div>
</form>

<table class="responsive-table responsive-table--banded">
	<thead>
		<tr>
			<th scope="col">Code</th>
			<th scope="col">User</th>
			<th scope="col">Queued</th>
			<th scope="col">Attempts</th>
			<th scope="col">From</th>
			<th scope="col">Subject</th>
			<th scope="col">MIME</th>
			<th scope="col">Content</th>
		</tr>
	</thead>
	<tbody>
<?php	if (count($messages) < 1) { ?>
		<tr>
			<td colspan="8">No messages found</td>
		</tr>
<?php	} else { foreach ($messages as $message) {
			$preview = trim(preg_replace('/\s+/', ' ', strip_tags((string)$message->content)));
			if (strlen($preview) > 80) $preview = substr($preview, 0, 77).'...';
			$deliveryUrl = '/_email/admin_deliveries?message_code='.urlencode($message->code);
?>
		<tr>
			<td data-label="Code"><a href="<?=htmlspecialchars($deliveryUrl, ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($message->code, ENT_QUOTES, 'UTF-8')?></a></td>
			<td data-label="User"><?=htmlspecialchars($message->user_code, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="Queued"><?=htmlspecialchars($message->date_queued, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="Attempts"><?=htmlspecialchars((string)$message->count_attempt, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="From"><?=htmlspecialchars($message->from_address, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="Subject"><a href="<?=htmlspecialchars($deliveryUrl, ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($message->subject, ENT_QUOTES, 'UTF-8')?></a></td>
			<td data-label="MIME"><?=htmlspecialchars($message->mime_type, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="Content"><?= $preview !== '' ? htmlspecialchars($preview, ENT_QUOTES, 'UTF-8') : '—' ?></td>
		</tr>
<?php	} } ?>
	</tbody>
</table>
