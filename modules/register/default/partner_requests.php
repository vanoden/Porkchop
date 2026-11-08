<?=$page->showTitle()?>
<?=$page->showMessages()?>

<p>Use this form to <strong>request access</strong> to another organization's data. Sending a request does not share your data, and approving a request later also does not share data until the other organization explicitly grants shares.</p>

<p><a href="/_register/partners">View partnerships where you share data</a> · <a href="/_register/organization">Back to organization</a></p>

<?php if ($can_manage) { ?>
<section>
	<h2>New Access Request</h2>
	<p>Active requests: <?=$active_count?> / <?= \Register\Partner\Request::MAX_ACTIVE ?>. Requests expire after one week.</p>
<?php	if ($active_count >= \Register\Partner\Request::MAX_ACTIVE) { ?>
	<p>You already have the maximum number of active requests. Cancel one, or wait for approval, denial, or expiry before creating another.</p>
<?php	} else { ?>
	<form method="post">
		<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		<label for="email">Deliver request to email</label>
		<input type="email" id="email" name="email" required placeholder="partner@example.com" value="<?=htmlspecialchars($_REQUEST['email'] ?? '', ENT_QUOTES, 'UTF-8')?>">
		<p>An email is sent only if this address belongs to a user in the system. The message explains that clicking the link and approving the partnership do not share data.</p>
		<button type="submit" name="method" value="Create Request">Create Request</button>
	</form>
<?php	} ?>
</section>
<?php } else { ?>
<p>Organization managers can create partnership access requests.</p>
<?php } ?>

<section>
	<h2>Your Requests</h2>
	<table class="table--banded">
		<thead>
			<tr>
				<th scope="col">Requested</th>
				<th scope="col">Email</th>
				<th scope="col">Status</th>
				<th scope="col">Expires</th>
				<th scope="col">Response</th>
				<th scope="col">Actions</th>
			</tr>
		</thead>
		<tbody>
<?php	if (empty($requests)) { ?>
			<tr><td colspan="6">No partnership requests yet.</td></tr>
<?php	} else {
			foreach ($requests as $request) { ?>
			<tr>
				<td data-label="Requested"><?=htmlspecialchars($request->date_request ?? '')?></td>
				<td data-label="Email"><?=htmlspecialchars($request->email ?? '')?></td>
				<td data-label="Status"><?=htmlspecialchars($request->status ?? '')?></td>
				<td data-label="Expires"><?=htmlspecialchars($request->date_expires ?? '')?></td>
				<td data-label="Response"><?=htmlspecialchars($request->date_response ?? '')?></td>
				<td data-label="Actions">
<?php			if ($can_manage && $request->status === \Register\Partner\Request::STATUS_ACTIVE) { ?>
					<form method="post" style="display:inline">
						<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
						<input type="hidden" name="request_id" value="<?=(int)$request->id?>">
						<button type="submit" name="method" value="Cancel">Cancel</button>
					</form>
<?php			} else { ?>
					—
<?php			} ?>
				</td>
			</tr>
<?php		}
		} ?>
		</tbody>
	</table>
</section>
