<?=$page->showTitle()?>
<?=$page->showMessages()?>

<section>
	<h2>Partnership Access Request</h2>

<?php if (!$ready) { ?>
	<p>This partnership request could not be loaded. The link may be invalid or expired.</p>
<?php } else { ?>
	<p><strong><?=htmlspecialchars(trim(($requester->first_name ?? '').' '.($requester->last_name ?? '')) ?: ($requester->code ?? 'A user'))?></strong>
	from <strong><?=htmlspecialchars($requester_org->name ?? 'an organization')?></strong>
	is requesting access to data from your organization.</p>

	<div class="alert">
		<p><strong>Approving this request does not share data.</strong> It only creates a partnership link so your organization can later specify which resources, if any, the partner may see or edit.</p>
		<p>Access is one-directional. Approving does not give them reciprocal rights to share your data elsewhere, and does not grant you access to their data.</p>
	</div>

	<table class="table--banded">
		<thead>
			<tr>
				<th scope="col">Status</th>
				<th scope="col">Requested</th>
				<th scope="col">Expires</th>
				<th scope="col">Delivered To</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td data-label="Status"><?=htmlspecialchars($request->status ?? '')?></td>
				<td data-label="Requested"><?=htmlspecialchars($request->date_request ?? '')?></td>
				<td data-label="Expires"><?=htmlspecialchars($request->date_expires ?? '')?></td>
				<td data-label="Delivered To"><?=htmlspecialchars($request->email ?? '')?></td>
			</tr>
		</tbody>
	</table>

<?php	if ($can_respond) { ?>
	<form method="post">
		<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		<input type="hidden" name="token" value="<?=htmlspecialchars($token, ENT_QUOTES, 'UTF-8')?>">
		<button type="submit" name="method" value="Approve">Approve Partnership</button>
		<button type="submit" name="method" value="Deny">Deny Request</button>
	</form>
<?php	} elseif ($request->status === \Register\Partner\Request::STATUS_APPROVED) { ?>
	<p>This partnership is approved. <a href="/_register/partners">Manage shared resources</a>.</p>
<?php	} elseif ($request->status === \Register\Partner\Request::STATUS_ACTIVE) { ?>
	<p>You must be signed in as a member of the invited organization to approve or deny this request.</p>
<?php	} ?>
<?php } ?>
</section>
