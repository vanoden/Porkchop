<script>
	function updateReport() {
		document.forms[0].filtered.value = 1;
		document.forms[0].submit();
		return true;
	}
</script>
<h2>Action Report</h2>
<div>
	<?	if ($page->errorCount()) { ?>
	<div class="form_error"><?=$page->errorString()?></div>
	<?	} ?>
	<h3>Filters</h3>
	<form name="reportForm" method="get" action="/_support/admin_actions">
	<input type="hidden" name="filtered" value="<?=$_REQUEST['filtered']?>" />
	<span class="label">Assigned To</span>
	<select name="assigned_id" class="value input" name="assigned_to" onchange="updateReport()" />
		<option value="">Any</option>
<?	foreach ($admins as $admin) { ?>
		<option value="<?=$admin->id?>"<? if ($_REQUEST['assigned_id'] == $admin->id) print " selected"; ?>><?=$admin->full_name()?></option>
<?	} ?>
	</select>
	
	<span class="label">Status</span>
	<div class="checkbox-row">
		<input type="checkbox" name="status_new" value="1" onclick="updateReport()"<? if ($_REQUEST['status_new']) print " checked";?> />
		<span class="value">NEW</span>
		<input type="checkbox" name="status_active" value="1" onclick="updateReport()"<? if ($_REQUEST['status_active']) print " checked";?> />
		<span class="value">ACTIVE</span>
		<input type="checkbox" name="status_pending_customer" value="1" onclick="updateReport()"<? if ($_REQUEST['status_pending_customer']) print " checked";?> />
		<span class="value">PENDING CUSTOMER</span>
		<input type="checkbox" name="status_pending_vendor" value="1" onclick="updateReport()"<? if ($_REQUEST['status_pending_vendor']) print " checked";?> />
		<span class="value">PENDING VENDOR</span>
		<input type="checkbox" name="status_cancelled" value="1" onclick="updateReport()"<? if ($_REQUEST['status_cancelled']) print " checked";?> />
		<span class="value">CANCELLED</span>
		<input type="checkbox" name="status_complete" value="1" onclick="updateReport()"<? if ($_REQUEST['status_complete']) print " checked";?> />
		<span class="value">COMPLETE</span>
	</div>
	</form>
</div>



<h2>Actions</h2>
<table>
<tr><th>Request Date</th>
	<th>Requested By</th>
	<th>Assigned To</th>
	<th>Action Type</th>
	<th>Status</th>
	<th>Device</th>
</tr>
<?	foreach ($actions as $action) {
	if ($action->assignedTo->id > 0) $assigned_to = $action->assignedTo->full_name();
	else $assigned_to = "Unassigned";
?>
<tr><td><a href="/_support/action/<?=$action->id?>"><?=$action->item->request->date_request?></a></td>
	<td><?=$action->item->request->customer->full_name()?></td>
	<td><?=$action->assignedTo->full_name()?></td>
	<td><?=$action->type?></td>
	<td><?=$action->status?></td>
	<td><?=$action->item->product->code?> - <?=$action->item->serial_number?></td>
</tr>
<?	} ?>
</table>