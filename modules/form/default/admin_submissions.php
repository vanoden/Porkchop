<?= $page->showAdminPageInfo(); ?>

<?php if (!$page->errorCount()) { ?>

<p><a href="/_form/admin_form/<?= htmlspecialchars((string)$form->code, ENT_QUOTES, 'UTF-8') ?>">« Back to form</a></p>

<p>Responses stored for this form (<code><?= htmlspecialchars((string)$form->code, ENT_QUOTES, 'UTF-8') ?></code>).</p>

<div class="table">
	<div class="tableRowHeader">
		<div class="tableCell">ID</div>
		<div class="tableCell">Submitted</div>
		<div class="tableCell">Version</div>
		<div class="tableCell">IP</div>
		<div class="tableCell">Answers</div>
	</div>
<?php if (empty($submissions)) { ?>
	<div class="tableRow">
		<div class="tableCell" colspan="5">No submissions yet.</div>
	</div>
<?php } else {
	foreach ($submissions as $sub) {
		$sid = (int)($sub->id ?? 0);
		$ver = new \Form\Version((int)($sub->version_id ?? 0));
		$verLabel = $ver->exists() ? (string)$ver->name : ('#' . (int)($sub->version_id ?? 0));
?>
	<div class="tableRow">
		<div class="tableCell"><?= $sid ?></div>
		<div class="tableCell"><?= htmlspecialchars((string)($sub->date_submitted ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
		<div class="tableCell"><?= htmlspecialchars($verLabel, ENT_QUOTES, 'UTF-8') ?></div>
		<div class="tableCell"><?= htmlspecialchars((string)($sub->remote_addr ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
		<div class="tableCell"><a href="/_form/admin_submission/<?= $sid ?>">View answers</a></div>
	</div>
<?php }
} ?>
</div>

<?php } ?>
