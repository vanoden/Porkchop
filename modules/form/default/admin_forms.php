<?= $page->showAdminPageInfo(); ?>

<a href="/_form/admin_form">Add New Form</a>
<div class="table">
	<div class="tableRowHeader">
		<div class="tableCell">Title</div>
		<div class="tableCell">Description</div>
		<div class="tableCell">Active Version</div>
		<div class="tableCell">Activated On</div>
	</div>
<?php	foreach ($forms as $form) {
			$av = $form->activeVersion();
	?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_form/admin_form/<?=$form->code?>"><?=$form->title?></a></div>
		<div class="tableCell"><?= strip_tags($form->description) ?></div>
		<div class="tableCell"><?= $av ? htmlspecialchars($av->name) : '—' ?></div>
		<div class="tableCell"><?= $av && $av->date_activated ? htmlspecialchars($av->date_activated) : '—' ?></div>
	</div>
<?php	} ?>
</div>
