<style>
	div.label {
		font-weight: bold;
		clear: left;
	}
	div.value {
		float: left;
	}
</style>
<div style="width: 756px;">
	<a href="/_support/requests" class="breadcrumbs">&gt; Requests</a>
	<a href="/_support/request/<?=$request->code?>" class="breadcrumbs">&gt; <?=$request->code?></a>
	<?php	if ($page->errorCount()) { ?>
	<div class="form_error"><?=$page->errorString()?></div>
	<?php	} ?>
	<form name="noteForm" method="post">
	<input type="hidden" name="request_id" value="<?=$request->id?>" />
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<h1>Request Action for <?=$request->code?></h1>
	<div class="container_narrow">
		<div class="label">Status</div>
		<select name="status">
			<option value="NEW">New</option>
			<option value="ASSIGNED">Assigned</option>
			<option value="ACTIVE">Active</option>
			<option value="PENDING CUSTOMER">Pending Customer</option>
			<option value="PENDING VENDOR">Pending Vendor</option>
			<option value="CANCELLED">Cancelled</option>
			<option value="COMPLETED">Completed</option>
			<option value="CLOSED">Closed</option>
		</select>
	</div>
	<div class="container_narrow">
		<div class="label">Date Requested</div>
		<input type="text" name="date_action" class="value input" value="<?=$_REQUEST['date_action']?>" />
	</div>
	<div class="container_narrow">
		<div class="label">Type</div>
		<select name="type">
			<option value="">Select</option>
			<option value="contact">Contact Customer</option>
			<option value="repair">Diagnose/Repair Device</option>
			<option value="repair">Order Part(s)</option>
			<option value="test">Test Device</option>
			<option value="ship">Ship Device</option>
			<option value="admin">Portal Administration</option>
		</select>
	</div>
	<div class="container_narrow">
		<div class="label">Requested By</div>
		<select class="value input" name="requestor_id">
			<option value="">Select</option>
		<?php	foreach ($users as $user) {
				if ($user->can('manage monitors')) continue;
		?>
			<option value="<?=$user->id?>"><?=$user->full_name()?></option>
		<?php	} ?>
		</select>
	</div>
	<div class="container_narrow">
		<div class="label">Assigned To</div>
		<select class="value input" name="assigned_id">
			<option value="">Select</option>
		<?php	foreach ($users as $user) {
				if ($user->can('manage monitors')) continue;
				if (! $user->can('manage customers')) continue;
		?>
			<option value="<?=$user->id?>"><?=$user->full_name()?></option>
		<?php	} ?>
		</select>
	</div>
	<div class="container_narrow">
		<div class="label">Device</div>
		<input type="text" name="asset_code" class="value input" />
	</div>
	<div class="container">
		<div class="label">Description</div>
		<textarea name="description"></textarea>
	</div>
	<div class="container">
		<input type="submit" name="btn_submit" class="button" value="Add Action" />
	</div>
	</form>
</div>
