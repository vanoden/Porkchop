<?= $page->showBreadcrumbs(); ?>
<?= $page->showTitle(); ?>
<?= $page->showMessages(); ?>
<div class="table">
	<div class="tableHeading">
		<div class="tableCell">Title</div>
		<div class="tableCell">Action</div>
	</div>
<?php	foreach ($forms as $form) { ?>
	<div class="tableRow">
		<div class="tableCell"><?=$form->title?></div>
		<div class="tableCell"><?=$form->action?></div>
	</div>
<?php	} ?>
</div>