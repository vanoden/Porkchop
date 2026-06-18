<?= $page->showAdminPageInfo(); ?>

<?php
$h = function ($s) {
	return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
};
?>

<?php if ($version->exists() && $form->exists()) { ?>
<ul class="connectBorder pageMessage warningText">
	<li class="pageMessage--warning">
		<strong>Preview</strong> — staff-only view of
		<?php if ($version->active()) { ?>
		this published version.
		<?php } else { ?>
		draft version &ldquo;<?= $h($version->name) ?>&rdquo; (not live for visitors).
		<?php } ?>
		Test submissions are saved like real ones.
		<a href="/_form/admin_version/<?= (int)$version->id ?>">Back to editor</a>
	</li>
</ul>
<?php } ?>

<?php if ($page->errorCount() > 0) { ?>
<section class="form-page-message">
	<p class="errorText"><?= $h($page->errorString()) ?></p>
</section>
<?php } elseif (! empty($page->success) && $formHtml === '') { ?>
<section class="form-page-message">
	<p class="progressText"><?= $h($page->success) ?></p>
<?php if ($version->exists()) { ?>
	<p><a href="/_form/admin_version/<?= (int)$version->id ?>">Back to editor</a>
	 · <a href="/_form/admin_version_preview/<?= (int)$version->id ?>">Preview again</a></p>
<?php } ?>
</section>
<?php } ?>

<?php if ($formHtml !== '') { ?>
<link rel="stylesheet" type="text/css" href="/css/form-maker-show.css">
<div class="porkchop-form-page">
<section class="porkchop-form-container formVersionPreviewContainer">
	<?= $formHtml ?>
</section>
</div>
<?php } ?>
