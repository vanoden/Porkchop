<style>
	td.column_code {
		width: 140px;
	}
	td.column_title {
		width: 220px;
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
<div class="breadcrumbs">
<a class="breadcrumb" href="/_engineering/home">Engineering</a>
</div>
<div class="title">Releases</div>
<a class="more" href="/_engineering/release">New Release</a>
<?	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorCount()?></div>
<?	}
	if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?	} ?>
<table class="body">
<tr><td class="label column_code">Code</td>
	<td class="label column_title">Title</td>
	<td class="label column_status">Status</td>
	<td class="label column_date">Released On</td>
</tr>
<?php
	foreach ($releases as $release) {
?>
<tr><td class="value"><a href="/_engineering/release/<?=$release->code?>"><?=$release->code?></a></td>
	<td class="value"><?=$release->title?></td>
	<td class="value"><?=$release->status?></td>
	<td class="value"><?=$release->date_released?></td>
<?php	} ?>
</table>
