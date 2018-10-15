<style>
	div.container {	width: 100%; clear: both;	}
	div.toggleContainer {	width: 100%; clear: both; display: none; }
</style>

<script>
	function showForm(form) {
		var forms = ['event','assign'];
		forms.forEach(function(form) {
			document.getElementById(form+'FormDiv').style.display = 'none';
		});
		var formDiv = document.getElementById(form+'FormDiv');
		formDiv.style.display = 'block';
		return true;
	}
	function hideForm(form) {
		var formDiv = document.getElementById(form+'FormDiv');
		formDiv.style.display = 'none';
		return true;
	}
</script>
<form name="action_form" method="post" action="/_support/action">
<input type="hidden" name="action_id" value="<?=$action->id?>" />
<div style="width: 756px;">
	<a href="/_support/requests" class="breadcrumbs">< Requests</a>
	<a href="/_support/request_detail/<?=$request->code?>" class="breadcrumbs">< Request <?=$request->code?></a>
	<a href="/_support/request_item/<?=$item->id?>" class="breadcrumbs">< Line <?=$item->line?></a>
	<input type="hidden" name="action_id" value="<?=$action->id?>" />
	<h1>Request Action <?=$request->code?>-<?=$item->line?>-<?=$action->id?></h1>
	<?	if ($page->errorCount()) { ?>
	<div class="form_error"><?=$page->errorString()?></div>
	<? } ?>
	<?	if ($page->success) { ?>
	<div class="form_success"><?=$page->success?></div>
	<?	} ?>
	<h2>Request <?=$request->code?></h2>
	<div class="container_narrow">
		<span class="label">Request Code</span>
		<span class="value"><?=$request->code?></span>
	</div>
	<div class="container_narrow">
		<span class="label">Request Date</span>
		<span class="value"><?=$request->date_request?></span>
	</div>
	<div class="container_narrow">
		<span class="label">Request Organization</span>
		<span class="value"><?=$request->customer->organization->name?></span>
	</div>
	<div class="container_narrow">
		<span class="label">Request Submitted By</span>
		<span class="value"><?=$request->customer->full_name()?></span>
	</div>
	<div class="container_narrow">
		<span class="label">Request Status</span>
		<span class="value"><?=$request->status?></span>
	</div>
	<div class="container_narrow">
		<span class="label">Item Line</span>
		<span class="value"><?=$item->line?></span>
	</div>
	<div class="container_narrow">
		<span class="label">Item Requested By</span>
		<span class="value"><?=$action->requestedBy->full_name()?></span>
	</div>
	<div class="container_narrow">
		<span class="label">Item Action Type</span>
		<span class="value"><?=$action->type?></span>
	</div>
	<div class="container_narrow">
		<span class="label">Assigned To</span>
		<span class="value"><?=$assignedTo?></span>
	</div>
	<div class="container_narrow">
		<span class="label">Status</span>
		<span class="value"><?=$action->status?></span>
	</div>
	<div class="container_narrow">
		<span class="label">Product</span>
		<span class="value"><?=$item->product->code?></span>
	</div>
	<div class="container_narrow">
		<span class="label">Serial Number</span>
		<span class="value"><?=$item->serial_number?></span>
	</div>
	<div class="container">
		<span class="label">Description</span>
		<span class="value"><?=$action->description?></span>
	</div>
	<div class="form_footer">
		<input type="button" name="btn_show" class="button" value="Add Event" onclick="showForm('event');" />
		<input type="button" name="btn_show" class="button" value="Assign Action" onclick="showForm('assign');" />
	</div>
	<div class="toggleContainer" id="eventFormDiv">
		<form name="eventForm" method="post" action="/_support/action">
		<input type="hidden" name="action_id" value="<?=$action->id?>" />
		<h2>Add Event</h2>
		<div class="container_narrow">
			<span class="label">Event Date</span>
			<input type="text" name="date_event" class="value input" value="now" />
		</div>
		<div class="container_narrow">
			<span class="label">User</span>
			<select name="user_id" class="value input">
				<option value="">Select</option>
	<?	foreach ($admins as $admin) { ?>
				<option value="<?=$admin->id?>"<? if ($admin->id == $GLOBALS['_SESSION_']->customer->id) print " selected"; ?>><?=$admin->full_name()?></option>
	<?	} ?>
			</select>
		</div>
		<div class="container_narrow">
			<span class="label">New Status</span>
			<select name="status" class="value input">
				<option value="ACTIVE">Active</option>
				<option value="PENDING CUSTOMER">Pending Customer</option>
				<option value="PENDING VENDOR">Pending Vendor</option>
				<option value="CANCELLED">Cancelled</option>
				<option value="COMPLETE">Complete</option>
			</select>
		</div>
		<div class="container">
			<span class="label">
				Description
			</span>
			<textarea name="description" class="value input" style="width: 650px"></textarea>
		</div>
		<div class="form_footer">
			<input type="submit" name="btn_add_event" class="button" value="Add Event" />
			<input type="button" name="btn_cancel" value="Cancel" class="button" onclick="hideForm('event');" />
		</div>
		</form>
	</div>
	<div class="toggleContainer" id="assignFormDiv">
		<form name="assignForm" method="post" action="/_support/action">
		<input type="hidden" name="action_id" value="<?=$action->id?>" />
		<h2>Assign Action</h2>
		<div class="container">
			<span class="label">User</span>
			<select name="assigned_id" class="value input">
				<option value="">Select</option>
	<?	foreach ($admins as $admin) { ?>
				<option value="<?=$admin->id?>"<? if ($admin->id == $GLOBALS['_SESSION_']->customer->id) print " selected"; ?>><?=$admin->full_name()?></option>
	<?	} ?>
			</select>
		</div>
		<div class="form_footer">
			<input type="submit" name="btn_assign_action" class="button" value="Assign Action" />
			<input type="button" name="btn_cancel" value="Cancel" class="button" onclick="hideForm('assign');" />
		</div>
		</form>
	</div>
	<h2>History</h2>
<?	foreach ($events as $event) { ?>
	<table style="width: 100%; padding-bottom: 10px;">
	<tr><th>Event Date</th>
		<th>User</th>
	</tr>
	<tr><td><?=$event->date_event?></td>
		<td><?=$event->user->full_name()?></td>
	</tr>
	<tr><th colspan="2">Description</th>
	<tr><td colspan="2"><?=$event->description?></td></tr>
	</tr>
	</table>
<?	} ?>
</div>