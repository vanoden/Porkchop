<link rel="stylesheet" href="/css/form-maker-show.css" type="text/css">

<?php if (isset($form) && $form->exists()) { ?>
<h1 class="formEmbedTitle"><?= htmlspecialchars(strip_tags($form->title), ENT_QUOTES, 'UTF-8') ?></h1>
<?php } ?>

<?= $page->showMessages() ?>

<div class="formMakerEmbed formMakerShow">
<?php
if (isset($form) && $form->exists() && empty($page->success)) {
	$form->render($extraHiddens ?? array());
}
?>
</div>
