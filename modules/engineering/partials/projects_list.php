<h2 style="display: inline-block;">Projects
    <?=($page->isSearchResults)? "[Matched Projects: ". count($projects)."]" : "";?>
</h2>
<?php
 if (!$page->isSearchResults) {
?>
    <a class="button more" href="/_engineering/project">New Project</a>
<?php
}
?>
<!--	START First Table -->
	<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 20%;">Code</div>
		<div class="tableCell" style="width: 25%;">Title</div>
		<div class="tableCell" style="width: 25%;">Description</div>
		<div class="tableCell" style="width: 30%;">Status</div>
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
		<div class="tableCell">
			<?=$project->status?>
		</div>
	</div>
<?php	} ?>
</div>
<!--	END First Table -->
