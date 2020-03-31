<div class="title">Modules</div>
<?php	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?php	} ?>
<div class="table">
	<div class="table_header">
		<div class="table_cell">Title</div>
		<div class="table_cell">Description</div>
	</div>
	<div class="table_body">
<?php	foreach ($modules as $module) { ?>
	<div class="table_row">
		<div class="table_cell"><?=$module->name()?></div>
		<div class="table_cell"><?=$module->description()?></div>
	</div>
<?php	} ?>
	</div>
</div>
