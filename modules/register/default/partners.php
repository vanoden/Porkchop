<?=$page->showTitle()?>
<?=$page->showMessages()?>

<p>Partnerships are one-directional. When you appear as the sharing organization, you control which resources a partner can access. Granting a partnership does not share data until you add implicit or explicit shares.</p>

<p><a href="/_register/partner_requests">Request access from another organization</a> · <a href="/_register/organization">Back to organization</a></p>

<section>
	<h2>Organizations You Share With</h2>
	<table class="table--banded">
		<thead>
			<tr>
				<th scope="col">Partner</th>
				<th scope="col">Created</th>
				<th scope="col">Actions</th>
			</tr>
		</thead>
		<tbody>
<?php	if (empty($sharing)) { ?>
			<tr><td colspan="3">No approved partnerships yet. Partnerships appear here after you approve an access request.</td></tr>
<?php	} else {
			foreach ($sharing as $entity) {
				$partner = $entity->partner(); ?>
			<tr>
				<td data-label="Partner"><?=htmlspecialchars($partner->name ?? '')?></td>
				<td data-label="Created"><?=htmlspecialchars($entity->date_created ?? '')?></td>
				<td data-label="Actions">
					<a href="/_register/partner_shares?partnership_id=<?=(int)$entity->id?>">Manage Shares</a>
<?php			if ($can_manage) { ?>
					<form method="post" style="display:inline" onsubmit="return confirm('Revoke this partnership and delete all shares?');">
						<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
						<input type="hidden" name="partnership_id" value="<?=(int)$entity->id?>">
						<button type="submit" name="method" value="Revoke">Revoke</button>
					</form>
<?php			} ?>
				</td>
			</tr>
<?php		}
		} ?>
		</tbody>
	</table>
</section>

<section>
	<h2>Organizations Sharing With You</h2>
	<table class="table--banded">
		<thead>
			<tr>
				<th scope="col">Organization</th>
				<th scope="col">Created</th>
			</tr>
		</thead>
		<tbody>
<?php	if (empty($receiving)) { ?>
			<tr><td colspan="2">No organizations are currently sharing data with you.</td></tr>
<?php	} else {
			foreach ($receiving as $entity) {
				$owner = $entity->organization(); ?>
			<tr>
				<td data-label="Organization"><?=htmlspecialchars($owner->name ?? '')?></td>
				<td data-label="Created"><?=htmlspecialchars($entity->date_created ?? '')?></td>
			</tr>
<?php		}
		} ?>
		</tbody>
	</table>
</section>
