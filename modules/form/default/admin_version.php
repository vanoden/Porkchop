<?= $page->showAdminPageInfo(); ?>

<style>
	/* Keep the choices editor visually nested under the main question row. */
	.formQuestionChoices {
		margin-top: 0.35em;
		padding: 0.45em 0.55em;
		border: 1px solid #d8d8d8;
		border-radius: 4px;
		background: #fafafa;
	}
	.formChoiceTable {
		width: 100%;
		border-collapse: collapse;
		font-size: 0.9em;
		background: #fff;
	}
	.formChoiceTable thead th {
		text-align: left;
		font-weight: 600;
		background: #d0d0d0;
		color: #000;
		border-bottom: 1px solid #d9d9d9;
		padding: 0.35em 0.45em;
	}
	.formChoiceTable td {
		padding: 0.3em 0.45em;
		border-bottom: 1px solid #ececec;
		vertical-align: middle;
	}
	.formChoiceTable tbody tr:last-child td {
		border-bottom: 0;
	}
	.formChoiceTable td:last-child,
	.formChoiceTable th:last-child {
		width: 3.5em;
		text-align: center;
	}
	.formChoiceTable input[type="text"] {
		width: 100%;
		box-sizing: border-box;
	}
	.formChoiceAddRow {
		margin-top: 0.4em;
	}
</style>

<form method="post">
<input type="hidden" name="csrf_token" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>" />
<input type="hidden" name="id" value="<?=$version->id?>" />
<input type="hidden" name="form_id" value="<?=$form->id?>" />

<?php if (! ($form->exists() && isset($version) && $version->exists())) { ?>
<label for="form_title">Form Title</label>
<span name="form_title"><?=$form->title?></span>
<?php } ?>

<?php
if ($form->exists() && isset($version) && $version->exists()) {
	$truncateUrl = function ($s) {
		$s = (string)$s;
		return strlen($s) > 20 ? substr($s, 0, 20) . '...' : $s;
	};
	$h = function ($s) {
		return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
	};
	$urlShow = '/_form/show/' . $form->code;
	$urlQuery = '/_form/show?code=' . rawurlencode($form->code);
	$urlEmbed = '/_form/embed/' . $form->code;
	$urlPreview = '/_form/preview/' . (int)$version->id;
?>
<div class="section">
	<div class="tableBody">
		<div class="tableRowHeader">
			<div class="tableCell">Item</div>
			<div class="tableCell">Value / URL</div>
		</div>
		<div class="tableRow">
			<div class="tableCell"><strong>Form title</strong></div>
			<div class="tableCell"><?= $h(strip_tags((string)$form->title)) ?></div>
		</div>
		<div class="tableRow">
			<div class="tableCell">Full page</div>
			<div class="tableCell"><a target="_blank" rel="noopener" href="<?= $h($urlShow) ?>" title="<?= $h($urlShow) ?>"><?= $h($truncateUrl($urlShow)) ?></a></div>
		</div>
		<div class="tableRow">
			<div class="tableCell">Same with query</div>
			<div class="tableCell"><a target="_blank" rel="noopener" href="<?= $h($urlQuery) ?>" title="<?= $h($urlQuery) ?>"><?= $h($truncateUrl($urlQuery)) ?></a> <span class="formUrlHint">(<code>?code=</code>)</span></div>
		</div>
		<div class="tableRow">
			<div class="tableCell">Embed (iframe)</div>
			<div class="tableCell"><a target="_blank" rel="noopener" href="<?= $h($urlEmbed) ?>" title="<?= $h($urlEmbed) ?>"><?= $h($truncateUrl($urlEmbed)) ?></a></div>
		</div>
		<div class="tableRow">
			<div class="tableCell">Preview this version <span class="formUrlHint">(draft or live; staff only; POST uses CSRF)</span></div>
			<div class="tableCell"><a target="_blank" rel="noopener" href="<?= $h($urlPreview) ?>" title="<?= $h($urlPreview) ?>"><?= $h($truncateUrl($urlPreview)) ?></a></div>
		</div>
	</div>
</div>
<?php } ?>

<label for="code">Version code</label>
<input type="text" name="code" value="<?= htmlspecialchars((string)($version->code ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Unique per form; leave blank to generate on save" />

<label for="name">Version Name</label>
<input type="text" name="name" value="<?=$version->name?>" />

<label for="description">Description</label>
<input type="text" name="description" value="<?=strip_tags($version->description)?>" />

<label for="instructions" style="display:block;margin:.35em 0">Instructions</label>
<textarea id="instructions" name="instructions"><?=$version->instructions?></textarea>

<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Type</div>
		<div class="tableCell">Question</div>
		<div class="tableCell">Prompt</div>
		<div class="tableCell">Required</div>
		<div class="tableCell">Choices</div>
	</div>
<?php	foreach($questions as $question) {
		$choiceTypes = array('select', 'radio', 'checkbox');
		$showChoices = in_array($question->type, $choiceTypes, true);
?>
	<div class="tableRow">
		<div class="tableCell">
			<select name="type[<?=$question->id?>]">
				<option value="text"<?php if ($question->type == "text") print " selected";?>>Text</option>
				<option value="textarea"<?php if ($question->type == "textarea") print " selected";?>>Textarea</option>
				<option value="select"<?php if ($question->type == "select") print " selected";?>>Select</option>
				<option value="checkbox"<?php if ($question->type == "checkbox") print " selected";?>>Checkbox</option>
				<option value="radio"<?php if ($question->type == "radio") print " selected";?>>Radio</option>
				<option value="submit"<?php if ($question->type == "submit") print " selected";?>>Submit</option>
				<option value="hidden"<?php if ($question->type == "hidden") print " selected";?>>Hidden</option>
			</select>
		</div>
		<div class="tableCell"><input type="text" name="text[<?=$question->id?>]" value="<?=strip_tags($question->text)?>" /></div>
		<div class="tableCell"><input type="text" name="prompt[<?=$question->id?>]" value="<?=$question->prompt?>" /></div>
		<div class="tableCell"><input type="checkbox" name="required[<?=$question->id?>]" value="1"<?php if ($question->required) print " checked";?> /></div>
		<div class="tableCell">
<?php	if ($showChoices) { ?>
			<div class="formQuestionChoices">
				<table class="formChoiceTable">
					<thead>
					<tr><th scope="col">Label</th><th scope="col">Value</th><th scope="col">Del</th></tr>
					</thead>
					<tbody>
<?php		foreach ($question->options() as $opt) { ?>
					<tr>
						<td><input type="text" name="option_text[<?= (int)$opt->id ?>]" value="<?= htmlspecialchars((string)$opt->text, ENT_QUOTES, 'UTF-8') ?>" size="18" maxlength="128" /></td>
						<td><input type="text" name="option_value[<?= (int)$opt->id ?>]" value="<?= htmlspecialchars((string)$opt->value, ENT_QUOTES, 'UTF-8') ?>" size="14" maxlength="128" /></td>
						<td><input type="checkbox" name="option_delete[<?= (int)$opt->id ?>]" value="1" title="Remove this choice" /></td>
					</tr>
<?php		} ?>
					</tbody>
					<tbody class="formChoiceNewRows" data-question-id="<?= (int)$question->id ?>">
					<tr class="formChoiceNewRow">
						<td><input type="text" name="option_new_text[<?= (int)$question->id ?>][]" value="" placeholder="Label" size="18" maxlength="128" autocomplete="off" /></td>
						<td><input type="text" name="option_new_value[<?= (int)$question->id ?>][]" value="" placeholder="Value" size="14" maxlength="128" autocomplete="off" /></td>
						<td><button type="button" class="formChoiceRemoveNewRow" title="Remove this row">&times;</button></td>
					</tr>
					</tbody>
				</table>
				<p><button type="button" class="formChoiceAddRow" data-question-id="<?= (int)$question->id ?>">Add choice row</button></p>
			</div>
<?php	} else { ?>
			<span class="formChoiceNa">—</span>
<?php	} ?>
		</div>
	</div>
<?php	} ?>
	<div class="tableRow">
		<div class="tableCell">
			<select name="type_new">
				<option value="text" selected>Text</option>
				<option value="textarea">Textarea</option>
				<option value="select">Select</option>
				<option value="checkbox">Checkbox</option>
				<option value="radio">Radio</option>
				<option value="hidden">Hidden</option>
			</select>
		</div>
		<div class="tableCell"><input type="text" name="text_new" value="" /></div>
		<div class="tableCell"><input type="text" name="prompt_new" value="" /></div>
		<div class="tableCell"><input type="checkbox" name="required_new" value="1" /></div>
		<div class="tableCell"><span class="formChoiceNa" title="Save the new question first, then add choices.">—</span></div>
	</div>
</div>
<div class="section">
	<input type="submit" name="submit" value="Save" />
</div>
<?php	if (isset($version) && $version->exists()) { ?>
<div class="section">
	<p><strong>Publication</strong>
	<?php	if ($version->active()) { ?>
		— <em>This version is live (visitors see it when the form is embedded or linked).</em>
	<?php	} else { ?>
		— <em>Draft — not the live version.</em>
	<?php	} ?>
	</p>
	<?php	if (! $version->active()) { ?>
	<p><input type="submit" name="publish_version" value="Publish this version" /></p>
	<?php	} ?>
	<?php	if (! empty($form->active_version_id)) { ?>
	<p><input type="submit" name="unpublish_form" value="Unpublish form (take offline)" onclick="return confirm('Take this form offline? Visitors will not see a live version until you publish a version again.');" /></p>
	<?php	} ?>
</div>
<?php	} ?>
</form>
<script>
(function () {
	function clearRowInputs(tr) {
		tr.querySelectorAll('input[type="text"]').forEach(function (inp) { inp.value = ''; });
	}
	document.querySelectorAll('.formChoiceAddRow').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var qid = btn.getAttribute('data-question-id');
			var tbody = document.querySelector('.formChoiceNewRows[data-question-id="' + qid + '"]');
			if (!tbody) return;
			var proto = tbody.querySelector('.formChoiceNewRow');
			if (!proto) return;
			var row = proto.cloneNode(true);
			clearRowInputs(row);
			tbody.appendChild(row);
		});
	});
	document.querySelectorAll('.formChoiceNewRows').forEach(function (tbody) {
		tbody.addEventListener('click', function (ev) {
			var t = ev.target;
			if (!t || !t.classList || !t.classList.contains('formChoiceRemoveNewRow')) return;
			var rows = tbody.querySelectorAll('.formChoiceNewRow');
			if (rows.length <= 1) {
				clearRowInputs(t.closest('tr'));
				return;
			}
			t.closest('tr').remove();
		});
	});
})();
</script>