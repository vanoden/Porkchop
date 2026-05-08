<?= $page->showAdminPageInfo(); ?>

<?php if (!$page->errorCount()) { ?>

<p><a href="/_form/admin_submissions/<?= htmlspecialchars((string)$form->code, ENT_QUOTES, 'UTF-8') ?>">« All submissions</a>
 · <a href="/_form/admin_form/<?= htmlspecialchars((string)$form->code, ENT_QUOTES, 'UTF-8') ?>">Form settings</a></p>

<dl style="margin:0.75rem 0 1rem;">
	<dt style="font-weight:600;display:inline;margin:0;padding:0;">Form</dt>
	<dd style="display:inline;margin:0 1.5rem 0 0.35rem;padding:0;">
		<?= htmlspecialchars((string)$form->title, ENT_QUOTES, 'UTF-8') ?>
	</dd>
	<dt style="font-weight:600;display:inline;margin:0;padding:0;">Submitted</dt>
	<dd style="display:inline;margin:0 1.5rem 0 0.35rem;padding:0;">
		<?= htmlspecialchars((string)($submissionMeta['date_submitted'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
	</dd>
	<dt style="font-weight:600;display:inline;margin:0;padding:0;">Version</dt>
	<dd style="display:inline;margin:0 1.5rem 0 0.35rem;padding:0;">
		<?= htmlspecialchars((string)($submissionMeta['version_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
	</dd>
	<dt style="font-weight:600;display:inline;margin:0;padding:0;">IP</dt>
	<dd style="display:inline;margin:0;padding:0;">
		<?= htmlspecialchars((string)($submissionMeta['remote_addr'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
	</dd>
</dl>

<p>Answer rows (<code>form_submission_answers</code>), including <code>aggregate_key</code> for cross-version reporting.</p>

<div class="table">
	<div class="tableRowHeader">
		<div class="tableCell">Answer ID</div>
		<div class="tableCell">submission_id</div>
		<div class="tableCell">question_id</div>
		<div class="tableCell">Question (text)</div>
		<div class="tableCell">Prompt</div>
		<div class="tableCell">aggregate_key</div>
		<div class="tableCell">value</div>
	</div>
<?php if (empty($answerRows)) { ?>
	<div class="tableRow">
		<div class="tableCell" colspan="7">No answer rows stored for this submission.</div>
	</div>
<?php } else {
	foreach ($answerRows as $row) {
?>
	<div class="tableRow">
		<div class="tableCell"><?= (int)($row['id'] ?? 0) ?></div>
		<div class="tableCell"><?= (int)($row['submission_id'] ?? 0) ?></div>
		<div class="tableCell"><?= (int)($row['question_id'] ?? 0) ?></div>
		<div class="tableCell"><code><?= htmlspecialchars((string)($row['question_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></div>
		<div class="tableCell"><?= htmlspecialchars((string)($row['question_prompt'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
		<div class="tableCell"><code><?= htmlspecialchars((string)($row['aggregate_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></div>
		<div class="tableCell"><?= htmlspecialchars((string)($row['value_display'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
	</div>
<?php }
} ?>
</div>

<?php } ?>
