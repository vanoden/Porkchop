<link rel="stylesheet" type="text/css" href="/css/form-maker-show.css">

<div class="porkchop-form-page">
<?php if ($page->errorCount() > 0) { ?>
	<section class="form-page-message">
		<p class="errorText"><?= htmlspecialchars($page->errorString(), ENT_QUOTES, 'UTF-8') ?></p>
	</section>
<?php } elseif (! empty($page->success)) { ?>
	<section class="form-page-message">
		<p class="progressText"><?= htmlspecialchars($page->success, ENT_QUOTES, 'UTF-8') ?></p>
	</section>
<?php } ?>

<?php if ($formHtml !== '') { ?>
	<section class="porkchop-form-container">
		<?= $formHtml ?>
	</section>
<?php } ?>
</div>
