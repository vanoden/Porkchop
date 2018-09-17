<style>
	td.column_code {
		width: 140px;
	}
	td.column_title {
		width: 240px;
	}
	td.column_date {
		width: 160px;
	}
	td.column_person {
		width: 160px;
	}
	td.column_status {
		width: 120px;
	}
	a.more {
		position: relative;
		display: block;
		font-weight: bold;
		padding-left: 15px;
		margin-top: 4px;
		font-size: 20px;
	}
	div.title {
		float: left;
		margin-right: 10px;
	}
	table.body {
		clear: both;
	}
</style>
<? if ($page->error) { ?>
<div class="form_error"><?=$page->error?></div>
<?	} ?>
<div class="title">Unassigned Tasks</div>
<a class="more" href="/_engineering/tasks">Manage Tasks</a>
<table class="body">
<tr><td class="label column_title">Title</td>
	<td class="label column_date">Added</td>
	<td class="label column_person">Requested By</td>
	<td class="label column_person">Assigned To</td>
	<td class="label column_status">Status</td>
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
<div class="title">Your Tasks</div>
<table class="body">
<tr><td class="label column_title">Title</td>
	<td class="label column_date">Added</td>
	<td class="label column_person">Requested By</td>
	<td class="label column_person">Assigned To</td>
	<td class="label column_status">Status</td>
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
<div class="title">Releases</div>
<a class="more" href="/_engineering/releases">Manage Releases</a>
<table class="body">
<tr><td class="label column_title">Title</td>
	<td class="label column_status">Status</td>
	<td class="label column_date">Scheduled For</td>
	<td class="label column_date">Released On</td>
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
<div class="title">Projects</div>
<a class="more" href="/_engineering/projects">Manage Projects</a>
<table class="body">
<tr><td class="label column_title">Title</td>
	<td class="label column_title">Manager</td>
	<td class="label" style="width: 600px">Description</td>
</tr>
<?php
	foreach ($projects as $project) {
?>
<tr><td class="value"><a href="/_engineering/project/<?=$project->code?>"><?=$project->title?></a></td>
	<td class="value"><?=$project->manager->code?></td>
	<td class="value"><?=$project->description?></td>
<?php	} ?>
</table>
<br>
<div class="title">Products</div>
<a class="more" href="/_engineering/products">Manage Products</a>
<table class="body">
<tr><td class="label column_code">Code</td>
	<td class="label column_title">Title</td>
	<td class="label" style="width: 600px">Description</td>
</tr>
<?php
	foreach ($products as $product) {
?>
<tr><td class="value"><a href="/_engineering/product/<?=$product->code?>"><?=$product->title?></a></td>
	<td class="value"><?=$product->description?></td>
<?php	} ?>
</table>
