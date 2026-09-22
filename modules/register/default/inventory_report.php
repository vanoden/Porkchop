<?= $page->showAdminPageInfo(); ?>

<form id="inventoryReportForm" method="get" action="/_register/inventory_report" class="monitor-admin-list register-inventory-report">
	<div class="filter-bar filter-bar--inline">
		<div class="filter-bar__controls">
			<div class="form-field">
				<label for="organization_id">Company</label>
				<select name="organization_id" id="organization_id" class="value input">
					<option value="">Select a company</option>
<?php	foreach ($organizations as $organization) { ?>
					<option value="<?=$organization->id?>"<?php if (!empty($selected_organization->id) && $organization->id == $selected_organization->id) print ' selected'; ?>><?=htmlspecialchars($organization->name)?></option>
<?php	} ?>
				</select>
			</div>
		</div>
		<div class="button-group filter-bar__actions">
			<button type="submit" name="btn_submit" value="Search">Search</button>
		</div>
	</div>

<?php	if (!$search_ran) { ?>
	<p>Select a company, then click Search.</p>
<?php	} else { ?>
	<h2><?=htmlspecialchars($selected_organization->name)?> — Active Devices by Product [<?=$device_total?>]</h2>
	<table class="responsive-table responsive-table--banded">
		<thead>
			<tr>
				<th scope="col" class="col-w-20">Product Code</th>
				<th scope="col" class="col-w-60">Product</th>
				<th scope="col" class="col-w-20">Active Devices</th>
			</tr>
		</thead>
		<tbody>
<?php	if (!empty($inventory_rows)) {
		foreach ($inventory_rows as $row) { ?>
			<tr>
				<td data-label="Product Code">
<?php		if (!empty($row->product_code)) { ?>
					<a href="/_product/edit/<?=htmlspecialchars($row->product_code, ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($row->product_code)?></a>
<?php		} ?>
				</td>
				<td data-label="Product"><?=htmlspecialchars($row->product_name)?></td>
				<td data-label="Active Devices"><?=htmlspecialchars((string)$row->count)?></td>
			</tr>
<?php	}
	} else { ?>
			<tr>
				<td colspan="3">No active devices found for this company</td>
			</tr>
<?php	} ?>
		</tbody>
	</table>
<?php	} ?>
</form>
