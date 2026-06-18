<?= $page->showAdminPageInfo(); ?>

<p class="formAdminFormsToolbar">
	<a class="button" href="/_form/admin_form">Add New Form</a>
</p>

<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Title</div>
		<div class="tableCell">Description</div>
		<div class="tableCell">Active Version</div>
		<div class="tableCell">Activated On</div>
		<div class="tableCell">Submissions</div>
	</div>
<?php	foreach ($forms as $form) {
		$av = $form->activeVersion();
		$description = trim(strip_tags((string)$form->description));
?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_form/admin_form/<?= htmlspecialchars((string)$form->code, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$form->title, ENT_QUOTES, 'UTF-8') ?></a></div>
		<div class="tableCell"><?= $description !== '' ? htmlspecialchars($description, ENT_QUOTES, 'UTF-8') : '<span class="formReadonlyEmpty">&mdash;</span>' ?></div>
		<div class="tableCell"><?= $av ? htmlspecialchars((string)$av->name, ENT_QUOTES, 'UTF-8') : '<span class="formReadonlyEmpty">&mdash;</span>' ?></div>
		<div class="tableCell"><?= $av && $av->date_activated ? htmlspecialchars((string)$av->date_activated, ENT_QUOTES, 'UTF-8') : '<span class="formReadonlyEmpty">&mdash;</span>' ?></div>
		<div class="tableCell"><a class="tableActionLink" href="/_form/admin_submissions/<?= htmlspecialchars((string)$form->code, ENT_QUOTES, 'UTF-8') ?>">Answers</a></div>
	</div>
<?php	} ?>
</div>
