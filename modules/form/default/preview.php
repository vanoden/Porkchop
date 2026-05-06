<?= $page->showTitle() ?>

<?= $page->showMessages() ?>

<p class="formPreviewNote"><em>Staff preview — this is the selected version, not necessarily the published one.<br />
Public URL: <a href="/_form/show/<?= htmlspecialchars($form->code, ENT_QUOTES, 'UTF-8') ?>">/_form/show/<?= htmlspecialchars($form->code, ENT_QUOTES, 'UTF-8') ?></a> (published version only).</em></p>

<div class="formMakerShow">
<?php
if (isset($form) && $form->exists() && isset($version) && $version->exists() && empty($page->success)) {
	$form->render($extraHiddens ?? array(), $version);
}
?>
</div>
