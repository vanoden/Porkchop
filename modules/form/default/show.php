<?= $page->showTitle() ?>

<?= $page->showMessages() ?>

<div class="formMakerShow">
<?php
if (isset($form) && $form->exists() && empty($page->success)) {
	$form->render($extraHiddens ?? array());
}
?>
</div>
