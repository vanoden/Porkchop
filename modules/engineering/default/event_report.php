<?php include(MODULES.'/engineering/partials/search_bar.php'); ?>
<div style="width: 756px;">
	<h1>Event Report</h1>
	<?php	if ($page->errorCount()) { ?>
	<div class="form_error"><?=$page->errorString()?></div>
	<?php	} ?>
	<h2>Filters</h2>
	<form action="/_engineering/event_report" method="get">
	<table class="min-tablet">
	<tr><th>Start Date</th>
		<th>End Date</th>
		<th>Project</th>
		<th>Product</th>
		<th>User</th>
	</tr>
	<tr><td><input type="text" name="date_start" class="value input" value="<?=$_REQUEST['date_start']?>" /></td>
		<td><input type="text" name="date_end" class="value input" value="<?=$_REQUEST['date_end']?>" /></td>
		<td><select name="project_id" class="value input">
				<option value="">Any</option>
<?php	foreach ($projects as $project) { ?>
				<option value="<?=$project->id?>"<?php	if ($project->id == $_REQUEST['project_id']) print " selected"; ?>><?=$project->title?></option>
<?php	} ?>
			</select>
		</td>
		<td><select name="product_id" class="value input">
				<option value="">Any</option>
<?php	foreach ($products as $product) { ?>
				<option value="<?=$product->id?>"<?php	if ($product->id == $_REQUEST['product_id']) print " selected"; ?>><?=$product->title?></option>
<?php	} ?>
			</select>
		</td>
		<td><select name="user_id" class="value input">
				<option value="">Any</option>
<?php	foreach ($users as $user) { ?>
				<option value="<?=$user->id?>"<?php	if ($user->id == $_REQUEST['user_id']) print " selected"; ?>><?=$user->full_name()?></option>
<?php	} ?>
			</select>
		</td>
	</tr>
	<tr><td class="form_footer" colspan="5">
			<input type="submit" name="btn_submit" class="button" value="Filter Results" />
		</td>
	</tr>
	</table>
	</form>
	<h2>Events</h2>
	<?php	foreach ($events as $event) {
		$person = $event->person();
		$task = $event->task();
		$project = $task->project();
		$product = $task->product();
	?>
	<table class="min-tablet">
	<tr><th>Event Date</th>
		<th>Project</th>
		<th>Product</th>
		<th>Task</th>
		<th>User</th>
	</tr>
	<tr><td><?=$event->date_event?></td>
		<td><a href="/_engineering/project/<?=$project->code?>"><?=$project->title?></a></td>
		<td><a href="/_engineering/product/<?=$product->code?>"><?=$product->title?></a></td>
		<td><a href="/_engineering/task/<?=$task->code?>"><?=$task->title?></a></td>
		<td><a href="/_register/account/<?=$person->code?>"><?=$person->full_name()?></a></td>
	</tr>
	<tr><th colspan="5">Description</th></tr>
	<tr><td colspan="5"><?=$event->description?></td></tr>
	</table>
	<?php	} ?>
</div>
