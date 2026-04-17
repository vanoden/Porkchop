<style>
	.formMakerEmbed { font-family: system-ui, sans-serif; margin: 0; padding: 0.75rem; }
	.formMakerEmbed .formEmbedTitle { font-size: 1.1rem; margin: 0 0 0.75rem 0; }
	.formMakerEmbed .errorText { color: #a00; }
	.formMakerEmbed .progressText { color: #060; }

	.formMakerShow .porkchop-form {
		max-width: 860px;
		padding: 1rem 1.1rem;
		background: #fff;
		border: 1px solid #d8d8d8;
		border-radius: 8px;
	}
	.formMakerShow .form_instructions {
		margin-bottom: 0.95rem;
		padding: 0.7rem 0.85rem;
		background: #f9fbff;
		border-left: 4px solid #8aaad6;
	}
	.formMakerShow .formGroup {
		margin: 1rem 0 1.1rem 0;
		padding: 0.75rem 0.85rem 0.25rem 0.85rem;
		background: #f8fafc;
		border: 1px solid #d7e0ea;
		border-radius: 7px;
	}
	.formMakerShow .formGroupTitle {
		margin: 0 0 0.45rem 0;
		padding-bottom: 0.35rem;
		font-size: 1.05rem;
		font-weight: 700;
		border-bottom: 1px solid #d7e0ea;
	}
	.formMakerShow .formGroupInstructions {
		margin: 0 0 0.6rem 0;
		color: #475467;
		font-size: 0.95em;
	}
	.formMakerShow .formQuestion {
		margin-bottom: 0.95rem;
		padding-bottom: 0.8rem;
		border-bottom: 1px solid #ededed;
	}
	.formMakerShow .formQuestion:last-of-type {
		border-bottom: 0;
	}
	.formMakerShow .formQuestion > label {
		display: block;
		margin-bottom: 0.35rem;
		font-weight: 600;
	}
	.formMakerShow .formQuestionHelp,
	.formMakerShow .formQuestionPrompt {
		margin-bottom: 0.35rem;
		color: #4b5563;
		font-size: 0.94em;
	}
	.formMakerShow input[type="text"],
	.formMakerShow textarea,
	.formMakerShow select {
		width: min(100%, 560px);
		padding: 0.42rem 0.5rem;
		border: 1px solid #c9cfd6;
		border-radius: 4px;
		box-sizing: border-box;
	}
	.formMakerShow textarea {
		min-height: 110px;
		resize: vertical;
	}
	.formMakerShow .formQuestion label {
		display: block;
		line-height: 1.35;
		margin-bottom: 0.2rem;
	}
	.formMakerShow .formQuestion > label {
		margin-bottom: 0.35rem;
		font-weight: 600;
	}
	.formMakerShow .formQuestion input[type="radio"],
	.formMakerShow .formQuestion input[type="checkbox"] {
		margin-right: 0.35rem;
	}
	.formMakerShow .formSubmit {
		margin-top: 1rem;
	}
</style>

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
