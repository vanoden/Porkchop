<div class="title">Modules</div>
<?	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	} ?>
<div class="table">
	<div class="table_header">
		<div class="table_cell">Title</div>
		<div class="table_cell">Description</div>
	</div>
	<div class="table_body">
<?	foreach ($modules as $module) { ?>
	<div class="table_row">
		<div class="table_cell"><?=$module->name()?></div>
		<div class="table_cell"><?=$module->description()?></div>
	</div>
<?	} ?>
	</div>
</div>
