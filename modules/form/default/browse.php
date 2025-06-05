<?= $page->showBreadcrumbs(); ?>
<?= $page->showTitle(); ?>
<?= $page->showMessages(); ?>

<a href="/_form/edit">Add New Form</a>
<div class="table">
	<div class="tableRowHeader">
		<div class="tableCell">Title</div>
		<div class="tableCell">Description</div>
		<div class="tableCell">Action</div>
		<div class="tableCell">Method</div>
	</div>
<?php	foreach ($forms as $form) { ?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_form/edit/<?=$form->code?>"><?=$form->title?></a></div>
		<div class="tableCell"><?= strip_tags($form->description) ?></div>
		<div class="tableCell"><?=$form->action?></div>
		<div class="tableCell"><?=$form->method?></div>
	</div>
<?php	} ?>
</div>
