<style>
	a.shortcut {
		padding-left: 20px;
		font-size: 16px;
		text-decoration: none;
	}
</style>
<div style="display: table">
    <div style="display:table-cell; width: 650px;">
        <div class="title">Request Details</div>
    </div>
    <div style="display:table-cell; width: 250px;">
        <a href="/_spectros/admin_requests" style="float: right; font-weight: bold; text-decoration: none; font-size: 16px">&lt;&lt;Outstanding Requests</a>
    </div>
</div>
<?php	if (isset($GLOBALS['_page']->error)) { ?>
<div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?php	}
	if (isset($GLOBALS['_page']->success)) { ?>
<div class="form_success"><?=$GLOBALS['_page']->success?></div>
<?php	} ?>
<form name="request_form" method="post">
<input type="hidden" name="request_code" value="<?=$request->code?>"/>
<table class="body form">
<tr><td class="form_instruction" colspan="4">Fill out the form below to create a new Action Request</td></tr>
<tr><td class="label">Request</td>
	<td class="value"><?=$request->code?></td>
	<td class="label">Date</td>
	<td class="value"><?=$request->date_request?></td>
</tr>
<tr><td class="label">Status</td>
	<td class="value">
		<select name="status" class="value input">
		<?php	foreach ($statii as $status) { ?>
			<option value="<?=$status?>"<?php if ($status == $request->status) print " selected"; ?>><?=$status?></option>
		<?php	} ?>
		</select>
	</td>
	<td class="label">Assigned To</td>
	<td class="value">
		<select name="user_assigned" class="value input">
			<option value="">Assign to</option>
		<?php	foreach ($techs as $tech) { ?>
			<option value="<?=$tech->id?>"<?php if ($request->user_assigned == $tech->id) print " selected";?>><?=$tech->first_name?> <?=$tech->last_name?></option>
		<?php	} ?>
		</select>
	</td>
</tr>
<tr><td class="label">Organization</td>
    <td class="value"><?=$organization->name?></td>
	<td class="label">Customer</td>
    <td class="value"><?=$requester->first_name?> <?=$requester->last_name?></td>
</tr>
<tr><td class="label" valign="top">Description</td>
    <td class="value" colspan="3"><?=$request->description?></td>
</tr>
<tr><td class="form_footer" colspan="4">
		<input type="submit" name="submit" class="button" value="Update Request"/>
	</td>
</tr>
</table>
</form>
<form name="task_form" method="post">
<input type="hidden" name="request_code" value="<?=$request->code?>"/>
<table class="body form">
<tr><td class="form_instruction" colspan="4">Add a Task</td></tr>
<tr><td class="label">Requested</td>
	<td class="value"><input type="text" name="date_request" class="value input" value="<?=date("m/d/Y H:i:s") ?>" /></td>
	<td class="label">By</td>
	<td class="value">
		<select name="user_requested" class="value input">
<?php	foreach ($techs as $tech) { ?>
			<option value="<?=$tech->id?>"<?php if ($tech->id == $GLOBALS['_SESSION_']->customer->id) print " selected"; ?>><?=$tech->first_name?> <?=$tech->last_name?></option>
<?php	} ?>
		</select></td>
</tr>
<tr><td class="label">Type</td>
	<td class="value">
		<select name="type_id" class="value input">
			<option value="">Select</option>
<?php	foreach ($types as $type) { ?>
			<option value="<?=$type->id?>"><?=$type->code?></option>
<?php	} ?>
		</select>
	</td>
	<td class="label">Status</td>
	<td class="value">
		<select name="status" class="value input">
		<?php	foreach ($statii as $status) { ?>
			<option value="<?=$status?>"<?php if ($status == $request->status) print " selected"; ?>><?=$status?></option>
		<?php	} ?>
		</select>
	</td>
</tr>
<tr><td class="label">Assigned To</td>
	<td class="value">
		<select name="user_assigned" class="value input">
			<option value="0">Unassigned</option>
<?php	foreach ($techs as $tech) { ?>
			<option value="<?=$tech->id?>"<?php if ($tech->id == $GLOBALS['_SESSION_']->customer->id) print " selected"; ?>><?=$tech->first_name?> <?=$tech->last_name?></option>
<?php	} ?>
		</select>
	</td>
	<td class="label">Asset</td>
	<td class="value">
		<select name="asset_id" class="value input">
			<option value="">N/A</option>
<?php	foreach ($assets as $asset) { ?>
			<option value="<?=$asset->id?>"><?=$asset->code?></option>
<?php	} ?>
		</select>
	</td>
</tr>
<tr><td class="label">Description</td>
	<td class="value" colspan="3"><textarea class="value input" name="description" style="width: 97%; height: 60px"></textarea></td>
</tr>
<tr><td class="form_footer" colspan="4">
		<input type="submit" name="submit" value="Add Task"/>
	</td>
</tr>
</table>
</form>
<div class="title">Tasks<a href="/_action/admin_task?request=<?=$request->code?>" class="shortcut">Add a Task</a></div>
<table class="body form">
<tr><td class="label column_header">Date</td>
	<td class="label column_header">Type</td>
	<td class="label column_header">Status</td>
	<td class="label column_header">Requested By</td>
	<td class="label column_header">Assigned To</td>
</tr>
<?php	foreach ($tasks as $task) { ?>
<tr><td class="value"><a href="/_spectros/admin_task/<?= $task->id ?>"><?=$task->date_request?></a></td>
	<td class="value"><?=$task->type()?></td>
	<td class="value"><?=$task->status?></td>
	<td class="value"><?=$task->user_requested_name()?></td>
	<td class="value"><?=$task->user_assigned_name()?></td>
</tr>
<?php	} ?>
</table>
<div class="title">Events<a href="/_action/admin_event?request=<?=$request->code?>" class="shortcut">Add an Event</a></div>
<table>
<?php	foreach ($requestEvents as $event) { ?>
<tr><td class="label">Date</td>
	<td class="value"><?=$event["timestamp"]?></td>
	<td class="label">User</td>
	<td class="value"><?=$event["user"]?></td>
</tr>
<tr><td class="value" colspan="4"><?=$event["description"]?></td></tr>
<?php	} ?>
</table>
