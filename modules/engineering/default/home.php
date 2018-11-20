<style>
	td.column_code { width: 140px; }
	td.column_title { width: 240px; }
	td.column_date { width: 160px; }
	td.column_person { width: 160px; }
	td.column_status { width: 120px; }
	div.title { float: left; margin-right: 10px; }
	table.body { clear: both; }
</style>
<div class="breadcrumbs">
   <a href="/_engineering/home">Engineering</a>
</div>
<?php include(MODULES.'/engineering/partials/search_bar.php'); ?>
<? if ($page->error) { ?>
    <div class="form_error"><?=$page->error?></div>
<?	} ?>
<h2 style="display: inline-block;">Unassigned Tasks</h2>
<a class="button more" href="/_engineering/tasks">Manage Tasks</a>
<a class="button more" href="/_engineering/event_report">Event Report</a>
<table class="body">
<tr>
	<th class="label column_title">Title</th>
	<th class="label column_date">Added</th>
	<th class="label column_person">Requested By</th>
	<th class="label column_person">Assigned To</th>
	<th class="label column_status">Status</th>
</tr>
<?php
	foreach ($unassigned_tasks as $task) {
		$requestor = $task->requestedBy();
		$worker = $task->assignedTo();
?>
<tr><td class="value"><a href="/_engineering/task/<?=$task->code?>"><?=$task->title?></a></td>
	<td class="value"><?=$task->date_added?></td>
	<td class="value"><?=$requestor->login?></td>
	<td class="value"><?=$worker->login?></td>
	<td class="value"><?=$task->status?></td>
<?php	} ?>
</table>
<br>
<h2 style="display: inline-block;">Your Tasks</h2>
<table class="body">
<tr>
	<th class="label column_title">Title</th>
	<th class="label column_date">Added</th>
	<th class="label column_person">Requested By</th>
	<th class="label column_person">Assigned To</th>
	<th class="label column_status">Status</th>
</tr>
<?php
	foreach ($my_tasks as $task) {
		$requestor = $task->requestedBy();
		$worker = $task->assignedTo();
?>
<tr><td class="value"><a href="/_engineering/task/<?=$task->code?>"><?=$task->title?></a></td>
	<td class="value"><?=$task->date_added?></td>
	<td class="value"><?=$requestor->login?></td>
	<td class="value"><?=$worker->login?></td>
	<td class="value"><?=$task->status?></td>
<?php	} ?>
</table>
<br>
<h2 style="display: inline-block;">Releases</h2>
<a class="button more" href="/_engineering/releases">Manage Releases</a>
<table class="body">
<tr>
	<th class="label column_title">Title</th>
	<th class="label column_status">Status</th>
	<th class="label column_date">Scheduled For</th>
	<th class="label column_date">Released On</th>
</tr>
<?php
	foreach ($releases as $release) {
?>
<tr><td class="value"><a href="/_engineering/release/<?=$release->code?>"><?=$release->title?></a></td>
	<td class="value"><?=$release->status?></td>
	<td class="value"><?=$release->date_scheduled?></td>
	<td class="value"><?=$release->date_released?></td>
<?php	} ?>
</table>
<br>
<h2 style="display: inline-block;">Projects</h2>
<a class="button more" href="/_engineering/projects">Manage Projects</a>
<table class="body">
<tr>
	<th class="label column_title">Title</th>
	<th class="label column_title">Manager</th>
	<th class="label" style="width: 600px">Description</th>
</tr>
<?php
	foreach ($projects as $project) {
?>
<tr>
	<td class="value"><a href="/_engineering/project/<?=$project->code?>"><?=$project->title?></a></td>
	<td class="value"><?=$project->manager->code?></td>
	<td class="value"><?=$project->description?></td>
<?php	} ?>
</table>
<br>
<h2 style="display: inline-block;">Products</h2>
<a class="button more" href="/_engineering/products">Manage Products</a>
<table class="body">
<tr>
	<th class="label column_title">Title</th>
	<th class="label" style="width: 600px">Description</th>
</tr>
<?php
	foreach ($products as $product) {
?>
<tr><td class="value"><a href="/_engineering/product/<?=$product->code?>"><?=$product->title?></a></td>
	<td class="value"><?=$product->description?></td>
<?php	} ?>
</table>
