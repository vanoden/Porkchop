<div style="width: 756px;">
<form name="task_form" action="/_engineering/task" method="post">
<input type="hidden" name="task_id" value="<?=$task->id?>" />
<div class="breadcrumbs">
<a class="breadcrumb" href="/_engineering/home">Engineering</a>
<a class="breadcrumb" href="/_engineering/tasks">Tasks</a>
</div>
<div class="title">Engineering Task</div>
<?	if ($page->error) { ?>
<div class="form_error"><?=$page->error?></div>
<?	}
	if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?	} ?>
<div class="container_narrow">
	<div class="label">Code</div>
	<input type="text" name="code" class="value input" value="<?=$form['code']?>" />
</div>
<div class="container_narrow">
	<div class="label">Title</div>
	<input type="text" name="title" class="value input" style="width: 240px" value="<?=$form['title']?>" />
</div>
<div class="container_narrow">
	<div class="label">Product</div>
	<select name="product_id" class="value input">
		<option value="">Select</option>
<?	foreach ($products as $product) { ?>
		<option value="<?=$product->id?>"<? if ($product->id == $form['product_id']) print " selected"; ?>><?=$product->title?></option>
<?	} ?>
	</select>
</div>
<div class="container_narrow">
	<div class="label">Date Requested</div>
	<input type="text" name="date_added" class="value input" value="<?=$form['date_added']?>" />
</div>
<div class="container_narrow">
	<div class="label">Date Due</div>
	<input type="text" name="date_due" class="value input" value="<?=$form['date_due']?>" />
</div>
<div class="container_narrow">
	<div class="label">Time Estimate (hrs)</div>
	<input type="text" name="estimate" class="value input" value="<?=$form['estimate']?>" />
</div>
<div class="container_narrow">
	<div class="label">Type</div>
	<select name="type" class="value input">
		<option value="bug"<? if ($form['type'] == "BUG") print " selected"; ?>>Bug</option>
		<option value="feature"<? if ($form['type'] == "FEATURE") print " selected"; ?>>Feature</option>
		<option value="test"<? if ($form['type'] == "TEST") print " selected"; ?>>Test</option>
	</select>
</div>
<div class="container_narrow">
	<div class="label">Status</div>
	<select name="status" class="value input">
		<option value="new"<? if ($form['status'] == "NEW") print " selected"; ?>>New</option>
		<option value="hold"<? if ($form['status'] == "HOLD") print " selected"; ?>>Hold</option>
		<option value="active"<? if ($form['status'] == "ACTIVE") print " selected"; ?>>Active</option>
		<option value="cancelled"<? if ($form['status'] == "CANCELLED") print " selected"; ?>>Cancelled</option>
		<option value="complete"<? if ($form['status'] == "COMPLETE") print " selected"; ?>>Complete</option>
	</select>
</div>
<div class="container_narrow">
	<div class="label">Priority</div>
	<select name="priority" class="value input">
		<option value="normal"<? if ($form['type'] == "NORMAL") print " selected"; ?>>Normal</option>
		<option value="important"<? if ($form['type'] == "IMPORTANT") print " selected"; ?>>Important</option>
		<option value="urgent"<? if ($form['type'] == "URGENT") print " selected"; ?>>Urgent</option>
		<option value="critical"<? if ($form['type'] == "CRITICAL") print " selected"; ?>>Critical</option>
	</select>
</div>
<div class="container_narrow">
	<div class="label">Requested By</div>
	<select name="requested_id" class="value input" style="width: 240px">
		<option value="">Select</option>
<?	foreach($people as $person) { ?>
		<option value="<?=$person->id?>"<? if ($person->id == $form['requested_id']) print " selected"; ?>><?=$person->login?></option>
<?	} ?>
	</select>
</div>
<div class="container_narrow">
	<div class="label">Assigned To</div>
	<select name="assigned_id" class="value input" style="width: 240px">
		<option value="">Unassigned</option>
<?	foreach($people as $person) { ?>
		<option value="<?=$person->id?>"<? if ($person->id == $form['assigned_id']) print " selected"; ?>><?=$person->login?></option>
<?	} ?>
	</select>
</div>
<div class="container_narrow">
	<div class="label">Release</div>
	<select name="release_id" class="value input" style="width: 240px">
		<option value="">Not Scheduled</option>
<?	foreach($releases as $release) { ?>
		<option value="<?=$release->id?>"<? if ($release->id == $form['release_id']) print " selected"; ?>><?=$release->title?></option>
<?	} ?>
	</select>
</div>
<div class="container">
	<div class="label">Description</div>
	<textarea name="description" style="width: 700px; height: 200px;"><?=$form['description']?></textarea>
</div>
<div class="container">
	<input type="submit" name="btn_submit" class="button" value="Submit">
</div>
<?	if ($task->id) { ?>
<div class="container">
	<div class="label">Event</div>
	<textarea name="notes" style="width: 700px; height: 150px;"></textarea>
</div>
</form>
<?	foreach ($events as $event) {
	$person = $event->person();
?>
<label class="task_event_date"><?=$event->date_event?></label>
<label class="task_event_user"><?=$person->login?></label>
<label class="task_event_description"><?=$event->description?></label>
<?	} ?>
</div>
<?	}	?>
