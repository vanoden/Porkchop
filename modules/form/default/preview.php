<?= $page->showTitle() ?>

<?= $page->showMessages() ?>

<style>
	.formPreviewNote {
		margin: 0 0 1rem 0;
		padding: 0.65rem 0.8rem;
		background: #f6f8fa;
		border: 1px solid #d9e0e6;
		border-radius: 6px;
	}
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
		display: block;
		margin-top: 0.25rem;
		min-height: 110px;
		resize: vertical;
	}
	.formMakerShow .formQuestion input[type="radio"],
	.formMakerShow .formQuestion input[type="checkbox"] {
		margin-right: 0.35rem;
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
	.formMakerShow .formSubmit {
		margin-top: 1rem;
	}
</style>

<p class="formPreviewNote"><em>Staff preview — this is the selected version, not necessarily the published one.<br />
Public URL: <a href="/_form/show/<?= htmlspecialchars($form->code, ENT_QUOTES, 'UTF-8') ?>">/_form/show/<?= htmlspecialchars($form->code, ENT_QUOTES, 'UTF-8') ?></a> (published version only).</em></p>

<div class="formMakerShow">
<?php
if (isset($form) && $form->exists() && isset($version) && $version->exists() && empty($page->success)) {
	$form->render($extraHiddens ?? array(), $version);
}
?>
</div>
