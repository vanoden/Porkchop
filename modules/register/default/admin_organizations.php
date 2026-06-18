<script type="text/javascript">
	function resetPaginationAndSearch() {
		document.getElementById('pagination_start_id').value = '0';
	}
</script>

<?= $page->showAdminPageInfo(); ?>

<form id="orgSearch" method="get" class="monitor-admin-list register-admin-organizations">
	<input type="hidden" id="pagination_start_id" name="pagination_start_id" value="<?=htmlspecialchars((string)$pagination->startId(), ENT_QUOTES, 'UTF-8')?>">

	<div class="filter-bar">
		<div class="filter-bar__controls">
			<div class="form-field filter-bar__search">
				<label for="searchOrganizationInput">Organization</label>
				<input type="text" id="searchOrganizationInput" name="name" placeholder="organization name" value="<?=htmlspecialchars($_REQUEST['name'] ?? '', ENT_QUOTES, 'UTF-8')?>"/>
			</div>
			<div class="form-field">
				<label for="searchedTag">Tag</label>
				<select name="searchedTag" id="searchedTag">
					<option value="">All Tags</option>
<?php	foreach ($organizationTags as $tag) { ?>
					<option value="<?=htmlspecialchars($tag, ENT_QUOTES, 'UTF-8')?>"<?php if (($tag ?? '') == ($_REQUEST['searchedTag'] ?? '')) print " selected"; ?>><?=htmlspecialchars($tag)?></option>
<?php	} ?>
				</select>
			</div>
			<div class="form-field form-field--narrow">
				<label for="pagination_size">Per page</label>
				<input type="text" id="pagination_size" name="<?=$pagination->sizeElemName?>" class="register-organizations-pagination-size" value="<?=htmlspecialchars((string)$pagination->size(), ENT_QUOTES, 'UTF-8')?>"/>
			</div>
		</div>
		<div class="form-field form-field--checks register-admin-organizations-filter-checks">
			<span class="form-field__group-label">Status</span>
			<div class="form-field__check-options">
			<label class="check-field">
				<input type="checkbox" name="hidden" value="1" <?php if (!empty($_REQUEST['hidden'])) print "checked"; ?>>
				Hidden
			</label>
			<label class="check-field">
				<input type="checkbox" name="expired" value="1" <?php if (!empty($_REQUEST['expired'])) print "checked"; ?>>
				Expired
			</label>
			<label class="check-field">
				<input type="checkbox" name="deleted" value="1" <?php if (!empty($_REQUEST['deleted'])) print "checked"; ?>>
				Deleted
			</label>
			</div>
		</div>
		<div class="button-group filter-bar__actions">
			<button type="submit" id="searchOrganizationButton" name="btn_search" value="Search" onclick="resetPaginationAndSearch()">Search</button>
			<a class="button btn-secondary" href="<?=PATH?>/_register/admin_organization">Add Organization</a>
		</div>
	</div>

	<h2>Organizations [<?=$total_organizations?>]</h2>
	<table class="responsive-table responsive-table--banded">
		<thead>
			<tr>
				<th scope="col" class="col-w-15">ID</th>
				<th scope="col" class="col-w-30">Name</th>
				<th scope="col" class="col-w-10">Status</th>
				<th scope="col" class="col-w-10">Members</th>
				<th scope="col" class="col-w-10">Devices</th>
			</tr>
		</thead>
		<tbody>
<?php	if (is_array($organizations) && count($organizations)) {
		foreach ($organizations as $organization) { ?>
			<tr>
				<td data-label="ID"><a href="<?=PATH."/_register/admin_organization?organization_id=".$organization->id?>"><?=htmlspecialchars($organization->code)?></a></td>
				<td data-label="Name"><?=htmlspecialchars($organization->name)?></td>
				<td data-label="Status"><?=htmlspecialchars($organization->status)?></td>
				<td data-label="Members"><?=htmlspecialchars((string)$organization->activeHumans())?></td>
				<td data-label="Devices"><?=htmlspecialchars((string)$organization->activeDevices())?></td>
			</tr>
<?php	}
	} else { ?>
			<tr>
				<td colspan="5">No organizations found</td>
			</tr>
<?php } ?>
		</tbody>
	</table>

	<div class="pagination pagination-bar" id="pagination">
		<?=$pagination->renderPages()?>
	</div>
</form>
