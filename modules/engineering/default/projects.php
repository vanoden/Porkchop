<div class="breadcrumbs">
	<a href="/_engineering/home">Engineering</a> > Projects
</div>
<h2 style="display: inline-block;">Projects</h2>
<a class="button more" href="/_engineering/project">New Project</a>

<!--	START First Table -->
	<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 20%;">Code</div>
		<div class="tableCell" style="width: 25%;">Title</div>
		<div class="tableCell" style="width: 55%;">Description</div>
	</div>
<?php
	foreach ($projects as $project) {
?>
	<div class="tableRow">
		<div class="tableCell">
			<a href="/_engineering/project/<?=$project->code?>"><?=$project->code?></a>
		</div>
		<div class="tableCell">
			<?=$project->title?>
		</div>
		<div class="tableCell">
			<?=$project->description?>
		</div>
	</div>
<?php	} ?>
</div>
<!--	END First Table -->
