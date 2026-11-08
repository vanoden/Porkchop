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
				<label for="delivery_code">Delivery</label>
				<input type="text" id="delivery_code" name="delivery_code" value="<?=htmlspecialchars((string)($_REQUEST['delivery_code'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
			<div class="form-field form-field--narrow">
				<label for="successful">Result</label>
				<select id="successful" name="successful">
					<option value="">Any</option>
					<option value="1"<?php if (($_REQUEST['successful'] ?? '') === '1') print ' selected'; ?>>Successful</option>
					<option value="0"<?php if (($_REQUEST['successful'] ?? '') === '0') print ' selected'; ?>>Failed</option>
				</select>
			</div>
			<div class="form-field">
				<label for="date_attempt_start">Attempt after</label>
				<input type="text" id="date_attempt_start" name="date_attempt_start" placeholder="YYYY-MM-DD HH:MM:SS" value="<?=htmlspecialchars((string)($_REQUEST['date_attempt_start'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
			<div class="form-field">
				<label for="date_attempt_end">Attempt before</label>
				<input type="text" id="date_attempt_end" name="date_attempt_end" placeholder="YYYY-MM-DD HH:MM:SS" value="<?=htmlspecialchars((string)($_REQUEST['date_attempt_end'] ?? ''), ENT_QUOTES, 'UTF-8')?>" />
			</div>
		</div>
		<div class="button-group filter-bar__actions">
			<button type="submit">Search</button>
			<a class="button btn-secondary" href="/_email/admin_attempts">Clear</a>
		</div>
	</div>
</form>

<table class="responsive-table responsive-table--banded">
	<thead>
		<tr>
			<th scope="col">Attempted</th>
			<th scope="col">Message</th>
			<th scope="col">Delivery</th>
			<th scope="col">Result</th>
			<th scope="col">Code</th>
			<th scope="col">Server response</th>
		</tr>
	</thead>
	<tbody>
<?php	if (count($attempts) < 1) { ?>
		<tr>
			<td colspan="6">No attempts found</td>
		</tr>
<?php	} else { foreach ($attempts as $attempt) {
			$response = trim(preg_replace('/\s+/', ' ', (string)$attempt->server_response));
			if (strlen($response) > 160) $response = substr($response, 0, 157).'...';
			$deliveryUrl = '/_email/admin_deliveries?message_code='.urlencode($attempt->message_code);
?>
		<tr>
			<td data-label="Attempted"><?=htmlspecialchars($attempt->date_attempt, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="Message"><a href="<?=htmlspecialchars($deliveryUrl, ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($attempt->message_code, ENT_QUOTES, 'UTF-8')?></a></td>
			<td data-label="Delivery"><a href="/_email/admin_attempts?delivery_code=<?=urlencode($attempt->delivery_code)?>"><?=htmlspecialchars($attempt->delivery_code, ENT_QUOTES, 'UTF-8')?></a></td>
			<td data-label="Result"><?= $attempt->successful ? 'Successful' : 'Failed' ?></td>
			<td data-label="Code"><?=htmlspecialchars($attempt->response_code, ENT_QUOTES, 'UTF-8')?></td>
			<td data-label="Server response" title="<?=htmlspecialchars($attempt->server_response, ENT_QUOTES, 'UTF-8')?>"><?= $response !== '' ? htmlspecialchars($response, ENT_QUOTES, 'UTF-8') : '—' ?></td>
		</tr>
<?php	} } ?>
	</tbody>
</table>
