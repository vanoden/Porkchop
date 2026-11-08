<?=$page->showTitle()?>
<?=$page->showMessages()?>

<p><a href="/_register/partners">Back to partnerships</a></p>

<?php if (!$entity) { ?>
<p>Select a valid partnership to manage shares.</p>
<?php } else { ?>
<p>Sharing with <strong><?=htmlspecialchars($partner->name ?? '')?></strong>. Shares grant access to that partner organization only.</p>

<section>
	<h2>Implicit Shares</h2>
	<p>Grant access to a resource type with optional JSON filters. Unique per partnership, resource class, and filters.</p>
<?php	if ($can_manage) { ?>
	<form method="post">
		<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		<input type="hidden" name="partnership_id" value="<?=(int)$entity->id?>">
		<label for="implicit_resource_class">Resource class</label>
		<input type="text" id="implicit_resource_class" name="resource_class" required placeholder="\Product\Item">
		<label for="implicit_filters">Filters (JSON)</label>
		<input type="text" id="implicit_filters" name="filters" value="{}" placeholder='{"status":"ACTIVE"}'>
		<label><input type="checkbox" name="read_only" value="1" checked> Read only</label>
		<button type="submit" name="method" value="Add Implicit Share">Add Implicit Share</button>
	</form>
<?php	} ?>
	<table class="table--banded">
		<thead>
			<tr>
				<th scope="col">Resource</th>
				<th scope="col">Filters</th>
				<th scope="col">Access</th>
				<th scope="col">Actions</th>
			</tr>
		</thead>
		<tbody>
<?php	if (empty($implicit)) { ?>
			<tr><td colspan="4">No implicit shares.</td></tr>
<?php	} else {
			foreach ($implicit as $share) { ?>
			<tr>
				<td data-label="Resource"><?=htmlspecialchars($share->resource_class ?? '')?></td>
				<td data-label="Filters"><code><?=htmlspecialchars($share->filters ?? '')?></code></td>
				<td data-label="Access"><?=!empty($share->read_only) ? 'Read only' : 'Editable'?></td>
				<td data-label="Actions">
<?php			if ($can_manage) { ?>
					<form method="post" style="display:inline">
						<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
						<input type="hidden" name="partnership_id" value="<?=(int)$entity->id?>">
						<input type="hidden" name="share_id" value="<?=(int)$share->id?>">
						<button type="submit" name="method" value="Delete Implicit">Remove</button>
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

<section>
	<h2>Explicit Shares</h2>
	<p>Grant access to one specific record by resource class and id.</p>
<?php	if ($can_manage) { ?>
	<form method="post">
		<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		<input type="hidden" name="partnership_id" value="<?=(int)$entity->id?>">
		<label for="explicit_resource_class">Resource class</label>
		<input type="text" id="explicit_resource_class" name="resource_class" required placeholder="\Product\Item">
		<label for="explicit_resource_id">Resource id</label>
		<input type="number" id="explicit_resource_id" name="resource_id" required min="1" step="1">
		<label><input type="checkbox" name="read_only" value="1" checked> Read only</label>
		<button type="submit" name="method" value="Add Explicit Share">Add Explicit Share</button>
	</form>
<?php	} ?>
	<table class="table--banded">
		<thead>
			<tr>
				<th scope="col">Resource</th>
				<th scope="col">Id</th>
				<th scope="col">Access</th>
				<th scope="col">Actions</th>
			</tr>
		</thead>
		<tbody>
<?php	if (empty($explicit)) { ?>
			<tr><td colspan="4">No explicit shares.</td></tr>
<?php	} else {
			foreach ($explicit as $share) { ?>
			<tr>
				<td data-label="Resource"><?=htmlspecialchars($share->resource_class ?? '')?></td>
				<td data-label="Id"><?=(int)$share->resource_id?></td>
				<td data-label="Access"><?=!empty($share->read_only) ? 'Read only' : 'Editable'?></td>
				<td data-label="Actions">
<?php			if ($can_manage) { ?>
					<form method="post" style="display:inline">
						<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
						<input type="hidden" name="partnership_id" value="<?=(int)$entity->id?>">
						<input type="hidden" name="share_id" value="<?=(int)$share->id?>">
						<button type="submit" name="method" value="Delete Explicit">Remove</button>
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
<?php } ?>
