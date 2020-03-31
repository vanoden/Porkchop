<style>
	td.label {
		width: 130px;
	}
</style>
<div class="title">Request Details</div>
<?php	if ($GLOBALS['_page']->error) { ?>
<div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?php	}
	if ($GLOBALS['_page']->success) { ?>
<div class="form_success"><?=$GLOBALS['_page']->success?></div>
<?php	} ?>
<form name="request_form" method="post">
<input type="hidden" name="request_code" value="<?=$_REQUEST["code"]?>"/>
<table class="body form">
<tr><td class="label">Request</td>
	<td class="value"><a href="/_spectros/admin_request_detail/<?=$request->code?>"><?=$request->code?></a></td>
	<td class="label">Date</td>
	<td class="value"><?=$request->date_request?></td>
</tr>
<tr><td class="label">Status</td>
	<td class="value"><?=$request->status?></td>
	<td class="label">Assigned To</td>
	<td class="value"><?=$request->user_assigned?></td>
</tr>
<tr><td class="label">Organization</td>
    <td class="value"><?=$organization->name?></td>
	<td class="label">Customer</td>
    <td class="value"><?=$requester->first_name?> <?=$requester->last_name?></td>
</tr>
<tr><td class="label" valign="top">Description</td>
    <td class="value" colspan="3"><?=$request->description?></td>
</tr>
</table>
</form>
<form name="task_form" method="post">
<input type="hidden" name="request_code" value="<?=$_REQUEST["code"]?>"/>
<div class="title">Task Details</div>
<table class="body form">
<tr><td class="label">Date</td>
	<td class="value"><?=$task->date_request?></td>
	<td class="label">Status</td>
	<td class="value">
		<select name="status" class="value input">
		<?php	foreach ($statii as $status) { ?>
			<option value="<?=$status?>"<?php	if ($status == $task->status) print " selected"; ?>><?=$status?></option>
		<?php	} ?>
		</select>
	</td>
</tr>
<tr><td class="label">Requested By</td>
	<td class="value"><?=$task->user_requested_name()?></td>
	<td class="label">Asset</td>
	<td class="value">
		<select name="asset_id" class="value input">
			<option value="">N/A</option>
<?php	foreach ($assets as $asset) { ?>
			<option value="<?=$asset->id?>"<?php	if ($asset->id == $task->asset_id) print " selected"; ?>><?=$asset->code?></option>
<?php	} ?>
		</select>
	</td>
</tr>
<tr><td class="label">Type</td>
	<td class="value"><?=$task->type()?></td>
	<td class="label">Assigned To</td>
	<td class="value">
		<select name="user_assigned" class="value input">
			<option value="0">Unassigned</option>
<?php	foreach ($techs as $tech) { ?>
			<option value="<?=$tech->id?>"<?php	if ($tech->id == $task->user_assigned) print " selected"; ?>><?=$tech->first_name?> <?=$tech->last_name?></option>
<?php	} ?>
		</select>
	</td>
</tr>
<tr><td class="label">Description</td>
	<td class="value" colspan="3"><?=$task->description?></td>
</tr>
<tr><td class="form_footer" colspan="4">
		<input type="submit" name="submit" value="Update Task"/>
	</td>
</tr>
</table>
<?php	foreach ($tasks as $task) { ?>
<table class="body form">
<tr><td class="label">Title</td>
</tr>
</table>
<?php	} ?>
<form action="/_spectros/admin_task" method="post">
<input type="hidden" name="task_id" value="<?=$task->id?>"/>
<div class="title">Add Event</div>
<table class="body form">
<tr><td class="label">Date</td>
	<td class="value"><input type="text" name="date_event" class="value input" value="<?=date('m/d/Y H:i:s')?>"></td>
	<td class="label">Person</td>
	<td class="value">
		<select name="user" class="value input">
<?php	foreach ($techs as $tech) { ?>
			<option value="<?=$tech->id?>"<?php	if ($tech->id == $GLOBALS['_SESSION_']->customer->id) print " selected"; ?>><?=$tech->first_name?> <?=$tech->last_name?></option>
<?php	} ?>
		</select>
	</td>
	<td class="label">New Status</td>
	<td class="value">
		<select name="status" class="value input">
		<?php	foreach ($statii as $status) { ?>
			<option value="<?=$status?>"<?php	if ($status == $task->status) print " selected"; ?>><?=$status?></option>
		<?php	} ?>
		</select>
	</td>
</tr>
<tr><td class="label">Description</td>
	<td class="value" colspan="5"><textarea name="description" class="value input" style="width: 750px"></textarea></td>
</tr>
<tr><td class="form_footer" colspan="6"><input type="submit" name="submit" value="Add Event" class="button"/></td></tr>
</table>
</form>
<div class="title">Task Events</div>
<table class="body form">
<?php	foreach ($taskEvents as $event) { ?>
<tr><td class="label">Date</td>
	<td class="value"><?=$event["timestamp"]?></td>
	<td class="label">User</td>
	<td class="value"><?=$event["user"]?></td>
</tr>
<tr><td class="value" colspan="4"><pre><?=$event["description"]?></pre></td></tr>
<?php	} ?>
</table>
