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
				<label for="message_code">Message</label>
				<input type="text" id="message_code" name="message_code" value="<?=htmlspecialchars((string)($_REQUEST['message_code'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
			<div class="form-field">
				<label for="from_address">From</label>
				<input type="text" id="from_address" name="from_address" value="<?=htmlspecialchars((string)($_REQUEST['from_address'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
			<div class="form-field">
				<label for="to_address">To</label>
				<input type="text" id="to_address" name="to_address" value="<?=htmlspecialchars((string)($_REQUEST['to_address'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
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
				<label for="last_attempt_start">Last attempt after</label>
				<input type="text" id="last_attempt_start" name="last_attempt_start" placeholder="YYYY-MM-DD HH:MM:SS" value="<?=htmlspecialchars((string)($_REQUEST['last_attempt_start'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
			<div class="form-field">
				<label for="last_attempt_end">Last attempt before</label>
				<input type="text" id="last_attempt_end" name="last_attempt_end" placeholder="YYYY-MM-DD HH:MM:SS" value="<?=htmlspecialchars((string)($_REQUEST['last_attempt_end'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
		</div>
		<div class="form-field form-field--checks">
			<span class="form-field__group-label">Status</span>
			<div class="form-field__check-options">
<?php	foreach ($deliveryStatuses as $status) { ?>
				<label class="check-field">
					<input type="checkbox" name="status[]" value="<?=$status?>"<?php if (in_array($status, $selectedStatuses, true)) print ' checked'; ?>>
					<?=$status?>
				</label>
<?php	} ?>
			</div>
		</div>
		<div class="button-group filter-bar__actions">
			<button type="submit">Search</button>
			<a class="button btn-secondary" href="/_email/admin_deliveries">Clear</a>
		</div>
	</div>
</form>

<table class="responsive-table responsive-table--banded">
	<thead>
		<tr>
			<th scope="col">Message</th>
			<th scope="col">Delivery</th>
			<th scope="col">Status</th>
			<th scope="col">Type</th>
			<th scope="col">To</th>
			<th scope="col">From</th>
			<th scope="col">Queued</th>
			<th scope="col">Last attempt</th>
			<th scope="col">Attempts</th>
			<th scope="col">Subject</th>
			<th scope="col">MIME</th>
		</tr>
	</thead>
	<tbody>
<?php	if (count($deliveries) < 1) { ?>
		<tr>
			<td colspan="11">No deliveries found</td>
		</tr>
<?php	} else { foreach ($deliveries as $delivery) {
			$attemptUrl = '/_email/admin_attempts?delivery_code='.urlencode($delivery->code);
			$messageUrl = '/_email/admin_deliveries?message_code='.urlencode($delivery->message_code);
			$recipient = $delivery->name !== '' ? $delivery->name.' <'.$delivery->address.'>' : $delivery->address;
?>
		<tr>
			<td data-label="Message"><a href="<?=htmlspecialchars($messageUrl, ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($delivery->message_code, ENT_QUOTES, 'UTF-8')?></a></td>
			<td data-label="Delivery"><a href="<?=htmlspecialchars($attemptUrl, ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($delivery->code, ENT_QUOTES, 'UTF-8')?></a></td>
			<td data-label="Status"><?=htmlspecialchars($delivery->status, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="Type"><?=htmlspecialchars($delivery->type, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="To"><?=htmlspecialchars($recipient, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="From"><?=htmlspecialchars($delivery->from_address, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="Queued"><?=htmlspecialchars($delivery->date_queued, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="Last attempt"><?= $delivery->date_last_attempt !== '' ? htmlspecialchars($delivery->date_last_attempt, ENT_QUOTES, 'UTF-8') : '—' ?></td>
			<td data-label="Attempts"><?=htmlspecialchars((string)$delivery->count_attempts, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="Subject"><?=htmlspecialchars($delivery->subject, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="MIME"><?=htmlspecialchars($delivery->mime_type, ENT_QUOTES, 'UTF-8')?></td>
		</tr>
<?php	} } ?>
	</tbody>
</table>
