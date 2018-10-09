<div>
<form name="task_form" action="/_engineering/task" method="post">
<input type="hidden" name="task_id" value="<?=$task->id?>" />
<div class="breadcrumbs">
<a class="breadcrumb" href="/_engineering/home">Engineering</a>
<a class="breadcrumb" href="/_engineering/tasks">Tasks</a>
</div>
<div class="title">Engineering Task<? if ($form['code']) print " ".$form['code']; ?></div>
<?	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	}
	if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?	} ?>
<?	if (! isset($task->id)) { ?>
<div class="container_narrow">
	<div class="label">Code</div>
	<input type="text" name="code" class="value input" value="<?=$form['code']?>" />
</div>
<?	} ?>
	
<!--	Start First Row-->
<div class="row">
<div class="container_narrow">
	<div class="label">Title</div>
	<input type="text" name="title" class="value input wide_md" value="<?=$form['title']?>" />
</div>
<div class="container_narrow">
	<div class="label wide_sm">Product</div>
	<select name="product_id" class="value input">
		<option value="">Select</option>
<?	foreach ($products as $product) { ?>
		<option value="<?=$product->id?>"<? if ($product->id == $form['product_id']) print " selected"; ?>><?=$product->title?></option>
<?	} ?>
	</select>
</div>
<div class="container_narrow">
	<div class="label">Date Requested</div>
	<?	if (isset($task->id)) { ?>
	<span class="value"><?=$form['date_added']?></span>
	<?	} else { ?>
	<input type="text" name="date_added" class="value input" value="<?=$form['date_added']?>" />
	<?	} ?>
</div>
<div class="container_narrow">
	<div class="label">Date Due</div>
	<input type="text" name="date_due" class="value input" value="<?=$form['date_due']?>" />
</div>
</div>
<!--End first row-->
	
<!--	Start Second Row-->
<div class="row">
<div class="container_narrow">
	<div class="label">Time Estimate (hrs)</div>
	<input type="text" name="estimate" class="value input wide_xs" value="<?=$form['estimate']?>" />
</div>
<div class="container_narrow">
	<div class="label">Type</div>
	<select name="type" class="value input wide_xs">
		<option value="bug"<? if ($form['type'] == "BUG") print " selected"; ?>>Bug</option>
		<option value="feature"<? if ($form['type'] == "FEATURE") print " selected"; ?>>Feature</option>
		<option value="test"<? if ($form['type'] == "TEST") print " selected"; ?>>Test</option>
	</select>
</div>
<div class="container_narrow">
	<div class="label">Status</div>
	<?	if (isset($task->id)) { ?>
	<span class="value"><?=$task->status?></span>
	<?	} else { ?>
	<select name="status" class="value input">
		<option value="new"<? if ($form['status'] == "NEW") print " selected"; ?>>New</option>
		<option value="hold"<? if ($form['status'] == "HOLD") print " selected"; ?>>Hold</option>
		<option value="active"<? if ($form['status'] == "ACTIVE") print " selected"; ?>>Active</option>
		<option value="cancelled"<? if ($form['status'] == "CANCELLED") print " selected"; ?>>Cancelled</option>
		<option value="complete"<? if ($form['status'] == "COMPLETE") print " selected"; ?>>Complete</option>
	</select>
	<?	}	?>
</div>
<div class="container_narrow">
	<div class="label">Priority</div>
	<select name="priority" class="value input wide_xs">
		<option value="normal"<? if ($form['priority'] == "NORMAL") print " selected"; ?>>Normal</option>
		<option value="important"<? if ($form['priority'] == "IMPORTANT") print " selected"; ?>>Important</option>
		<option value="urgent"<? if ($form['priority'] == "URGENT") print " selected"; ?>>Urgent</option>
		<option value="critical"<? if ($form['priority'] == "CRITICAL") print " selected"; ?>>Critical</option>
	</select>
</div>
<!--End second row-->
	
<!--	Start Third Row-->	
<div class="row">
<div class="container_narrow">
	<div class="label">Requested By</div>
	<?	if (isset($task->id)) {
		$requestor = $task->requestedBy(); ?>
	<span class="value"><?=$requestor->first_name?> <?=$requestor->last_name?></span>
	<?	} else { ?>
	<select name="requested_id" class="value input" style="width: 240px">
		<option value="">Select</option>
<?	foreach($people as $person) { ?>
		<option value="<?=$person->id?>"<? if ($person->id == $form['requested_id']) print " selected"; ?>><?=$person->login?></option>
<?	} ?>
	</select>
	<?	}	?>
</div>
<div class="container_narrow">
	<div class="label">Assigned To</div>
	<select name="assigned_id" class="value input wide_sm">
		<option value="">Unassigned</option>
<?	foreach($techs as $person) { ?>
		<option value="<?=$person->id?>"<? if ($person->id == $form['assigned_id']) print " selected"; ?>><?=$person->login?></option>
<?	} ?>
	</select>
</div>
<div class="container_narrow">
	<div class="label">Release</div>
	<select name="release_id" class="value input wide_md">
		<option value="">Not Scheduled</option>
<?	foreach($releases as $release) { ?>
		<option value="<?=$release->id?>"<? if ($release->id == $form['release_id']) print " selected"; ?>><?=$release->title?></option>
<?	} ?>
	</select>
</div>
<div class="container_narrow">
	<div class="label">Project</div>
	<select name="project_id" class="value input wide_md">
		<option value="">No Project</option>
<?	foreach($projects as $project) { ?>
		<option value="<?=$project->id?>"<? if ($project->id == $form['project_id']) print " selected"; ?>><?=$project->title?></option>
<?	} ?>
	</select>
</div>
</div>	
<!--End third row-->
	
<div class="container">
	<div class="label">Description</div>
	<textarea name="description" style="width: 720px; height: 80px;"><?=$form['description']?></textarea>
</div>
<div class="container" class="form_footer">
	<input type="submit" name="btn_submit" class="button" value="Submit">
</div>
<br>
<?	if ($task->id) { ?>
<div class="container_narrow">
	<div class="label">Event Date</div>
	<input type="text" name="date_event" class="value input" value="<?=date('m/d/Y H:i:s')?>" />
</div>
<div class="container_narrow">
	<div class="label">Person</div>
	<select name="event_person_id" class="value input wide_xs">
<?	foreach ($people as $person) { ?>
		<option value="<?=$person->id?>"<? if ($person->id == $GLOBALS['_SESSION_']->customer->id) print " selected"; ?>><?=$person->code?></option>
<?	} ?>
	</select>
</div>
<div class="container_narrow">
	<div class="label">New Status</div>
	<select name="new_status" class="value input wide_xs">
		<option value="new"<? if ($task->status == 'NEW') print ' selected'; ?>>New</option>
		<option value="hold"<? if ($task->status == 'HOLD') print ' selected'; ?>>Hold</option>
		<option value="active"<? if ($task->status == 'ACTIVE') print ' selected'; ?>>Active</option>
		<option value="cancelled"<? if ($task->status == 'CANCELLED') print ' selected'; ?>>Cancelled</option>
		<option value="complete"<? if ($task->status == 'COMPLETE') print ' selected'; ?>>Complete</option>
	</select>
</div>
<div class="container">
	<div class="label">Event Description</div>
	<textarea name="notes" style="width: 720px; height: 60px;"></textarea>
</div>
<div class="form_footer">
	<input type="submit" name="btn_add_event" class="button" value="Add Event" />
</div>
</form>
<table class="body" style="width: 760px;">
<tr><td class="label">Date</td>
	<td class="label">Person</td>
</tr>
<?	foreach ($events as $event) {
	$person = $event->person();
?>
<tr><td class="value <?=$greenbar?>"><?=$event->date_event?></td>
	<td class="value <?=$greenbar?>"><?=$person->login?></td>
</tr>
<tr><td colspan="2" class="value <?=$greenbar?>" style="border-bottom: 1px solid gray"><?=$event->description?></td></tr>
<?		if ($greenbar) $greenbar = '';
		else $greenbar = 'greenbar';
	}
?>
</table>
<?	}	?>
