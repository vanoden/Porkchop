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
<div class="title">Projects</div>
<a class="more" href="/_engineering/project">New Project</a>
<table class="body">
<tr><td class="label column_code">Code</td>
	<td class="label column_title">Title</td>
	<td class="label" style="width: 600px">Description</td>
</tr>
<?php
	foreach ($projects as $project) {
?>
<tr><td class="value"><a href="/_engineering/project/<?=$project->code?>"><?=$project->code?></a></td>
	<td class="value"><?=$project->title?></td>
	<td class="value"><?=$project->description?></td>
<?php	} ?>
</table>
