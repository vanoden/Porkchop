<?= $page->showAdminPageInfo(); ?>

<?php
// Sticky-form helpers: when validation fails we re-render the form preferring
// what the user just typed over the DB state, so they don't lose their edits.
$hasErrors = (isset($page) && method_exists($page, 'errorCount') && $page->errorCount() > 0);
$h = function ($s) {
	return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
};
$postScalar = function ($name, $fallback = '') use ($hasErrors) {
	if ($hasErrors && isset($_POST[$name]) && is_scalar($_POST[$name])) {
		return (string)$_POST[$name];
	}
	return (string)$fallback;
};
$postArrScalar = function ($name, $key, $fallback = '') use ($hasErrors) {
	if ($hasErrors && isset($_POST[$name]) && is_array($_POST[$name]) && array_key_exists($key, $_POST[$name]) && is_scalar($_POST[$name][$key])) {
		return (string)$_POST[$name][$key];
	}
	return (string)$fallback;
};
$postCheckbox = function ($name, $fallback = false) use ($hasErrors) {
	if ($hasErrors) {
		return ! empty($_POST[$name]);
	}
	return (bool)$fallback;
};
$postArrCheckbox = function ($name, $key, $fallback = false) use ($hasErrors) {
	if ($hasErrors) {
		return ! empty($_POST[$name][$key]);
	}
	return (bool)$fallback;
};
$postArrList = function ($name, $key) use ($hasErrors) {
	if ($hasErrors && isset($_POST[$name][$key]) && is_array($_POST[$name][$key])) {
		return array_map('strval', $_POST[$name][$key]);
	}
	return array();
};
?>

<?php if (! empty($isLocked)) {
	$publishedDate = trim((string)($version->date_activated ?? ''));
	$activatedBy = trim((string)$version->activatedByDisplayName());
	$createDraftUrl = '/_form/admin_version/' . rawurlencode((string)$form->code);
	$groupsById = array();
	foreach ($groups as $group) {
		$groupsById[(int)$group->id] = $group;
	}
?>
<div class="formVersionReadonly">

  <ul class="connectBorder pageMessage warningText">
    <li class="pageMessage--warning">Read-only: this version has been published and cannot be edited.<?php
      if ($publishedDate !== '') { ?> Published <?= $h($publishedDate) ?><?php }
      if ($activatedBy !== '') { ?> by <?= $h($activatedBy) ?><?php }
    ?>. To make changes, create a new draft.
    </li>
  </ul>


  <table class="responsive-table">
    <thead>
      <tr>
        <th scope="col">Field</th>
        <th scope="col">Value</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td data-label="Field">Version code</td>
        <td data-label="Value"><?= $h($version->code) ?></td>
      </tr>
      <tr>
        <td data-label="Field">Version name</td>
        <td data-label="Value"><?= $h($version->name) ?></td>
      </tr>
      <tr>
        <td data-label="Field">Description</td>
        <td data-label="Value"><?= $h(strip_tags((string)$version->description)) ?></td>
      </tr>
      <tr>
        <td data-label="Field">Instructions</td>
        <td data-label="Value"><?= nl2br($h($version->instructions)) ?></td>
      </tr>
    </tbody>
  </table>

  <h3>Groups</h3>
  <table class="responsive-table">
    <thead>
      <tr>
        <th scope="col">Title</th>
        <th scope="col">Instructions</th>
        <th scope="col">View Order</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($groups)) { ?>
      <tr>
        <td data-label="Title">&mdash;</td>
        <td data-label="Instructions"></td>
        <td data-label="View Order"></td>
      </tr>
      <?php } else { foreach ($groups as $group) { ?>
      <tr>
        <td data-label="Title"><?= $h($group->title) ?></td>
        <td data-label="Instructions"><?= $h($group->instructions) ?></td>
        <td data-label="View Order"><?= (int)$group->sort_order ?></td>
      </tr>
      <?php }} ?>
    </tbody>
  </table>

  <h3>Questions</h3>
  <table class="responsive-table">
    <thead>
      <tr>
        <th scope="col">Type</th>
        <th scope="col">Question</th>
        <th scope="col">Prompt</th>
        <th scope="col">Required</th>
        <th scope="col">Group</th>
        <th scope="col">View Order</th>
        <th scope="col">Choices</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($questions)) { ?>
          <tr>
            <td colspan="7"><em>No questions defined.</em></td>
          </tr>
      <?php } else { foreach ($questions as $question) {
        $choiceTypes = array('select', 'radio', 'checkbox');
        $showChoices = in_array($question->type, $choiceTypes, true);
        $gid = (int)($question->group_id ?? 0);
        $groupName = ($gid > 0 && isset($groupsById[$gid])) ? (string)$groupsById[$gid]->title : '';
      ?>
      <tr>
        <td data-label="Type"><?= $h($question->type) ?></td>
        <td data-label="Question"><?= $h(strip_tags((string)$question->text)) ?></td>
        <td data-label="Prompt"><?= $h($question->prompt) ?></td>
        <td data-label="Required"><?= ! empty($question->required) ? 'Yes' : 'No' ?></td>
        <td data-label="Group"><?= $groupName !== '' ? $h($groupName) : '<em>Ungrouped</em>' ?></td>
        <td data-label="View Order"><?= (int)$question->sort_order ?></td>
        <td data-label="Choices">
          <?php if ($showChoices) {
            $opts = $question->options();
            if (empty($opts)) { ?>
                  <span class="formChoiceNa">&mdash;</span>
          <?php } else { ?>
                  <ul class="formChoiceListReadonly">
          <?php foreach ($opts as $opt) { ?>
                    <li><?= $h($opt->text) ?> <span class="formChoiceValueReadonly">(<?= $h($opt->value) ?>)</span></li>
          <?php } ?>
                  </ul>
          <?php }
          } else { ?>
                  <span class="formChoiceNa">&mdash;</span>
          <?php } ?>
        </td>
      </tr>
      <?php }} ?>
    </tbody>
  </table>

<div class="section formVersionReadonlyActions">
	<p><a class="formVersionAction" href="<?= $h($createDraftUrl) ?>">Create new draft from this version</a></p>
<?php	if ($version->active()) { ?>
	<form method="post" class="formVersionUnpublishForm">
		<input type="hidden" name="csrf_token" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>" />
		<input type="hidden" name="id" value="<?= (int)$version->id ?>" />
		<input type="hidden" name="form_id" value="<?= (int)$form->id ?>" />
		<p><input type="submit" name="unpublish_form" value="Unpublish form (take offline)" onclick="return confirm('Take this form offline? Visitors will not see a live version until you publish a version again.');" /></p>
	</form>
<?php	} ?>
</div>

</div>
<?php } else { ?>

<form method="post">
<input type="hidden" name="csrf_token" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>" />
<input type="hidden" name="id" value="<?=$version->id?>" />
<input type="hidden" name="form_id" value="<?=$form->id?>" />

<?php if (! ($form->exists() && isset($version) && $version->exists())) { ?>
<label for="form_title">Form Title</label>
<span name="form_title"><?=$form->title?></span>
<?php } ?>

<div class="section-grid grid-col-3">
  <div class="form-field">
  <label for="code">Version code</label>
  <input type="text" name="code" value="<?= $h($postScalar('code', $version->code ?? '')) ?>" placeholder="Unique per form; leave blank to generate on save" />
  </div>

  <div class="form-field">
  <label for="name">Version Name</label>
  <input type="text" name="name" value="<?= $h($postScalar('name', $version->name ?? '')) ?>" />
  </div>

  <div class="form-field">
  <label for="description">Description</label>
  <input type="text" name="description" value="<?= $h($postScalar('description', strip_tags((string)($version->description ?? '')))) ?>" />
  </div>
</div>

<div class="form-field">
<label for="instructions" style="display:block;margin:.35em 0">Instructions</label>
<textarea id="instructions" name="instructions"><?= $h($postScalar('instructions', $version->instructions ?? '')) ?></textarea>
</div>

<h3>Groups</h3>
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Title</div>
		<div class="tableCell">Instructions</div>
		<div class="tableCell">View Order</div>
	</div>
<?php foreach ($groups as $group) { ?>
	<div class="tableRow">
		<div class="tableCell"><input type="text" name="group_title[<?= (int)$group->id ?>]" value="<?= $h($postArrScalar('group_title', (int)$group->id, $group->title ?? '')) ?>" /></div>
		<div class="tableCell"><input type="text" name="group_instructions[<?= (int)$group->id ?>]" value="<?= $h($postArrScalar('group_instructions', (int)$group->id, $group->instructions ?? '')) ?>" /></div>
		<div class="tableCell"><input type="number" name="group_sort_order[<?= (int)$group->id ?>]" value="<?= (int)$postArrScalar('group_sort_order', (int)$group->id, (int)$group->sort_order) ?>" /></div>
	</div>
<?php } ?>
	<div class="tableRow">
		<div class="tableCell"><input type="text" name="group_title_new" value="<?= $h($postScalar('group_title_new', '')) ?>" placeholder="New group title" /></div>
		<div class="tableCell"><input type="text" name="group_instructions_new" value="<?= $h($postScalar('group_instructions_new', '')) ?>" placeholder="Optional instructions" /></div>
		<div class="tableCell"><input type="number" name="group_sort_order_new" value="<?= (int)$postScalar('group_sort_order_new', '50') ?>" /></div>
	</div>
</div>

<h3>Questions</h3>
<?php
$inheritedKeys = isset($inheritedKeys) && is_array($inheritedKeys) ? $inheritedKeys : array();
$groupsById = array();
foreach ($groups as $group) {
	$groupsById[(int)$group->id] = $group;
}
?>
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Type</div>
		<div class="tableCell">Question</div>
		<div class="tableCell">Prompt</div>
		<div class="tableCell">Required</div>
		<div class="tableCell">Group</div>
		<div class="tableCell">View Order</div>
		<div class="tableCell">Choices</div>
		<div class="tableCell">Delete</div>
	</div>
<?php	foreach($questions as $question) {
		$choiceTypes = array('select', 'radio', 'checkbox');
		$showChoices = in_array($question->type, $choiceTypes, true);
		$isInherited = ! empty($inheritedKeys[(string)$question->aggregate_key]);
		$qGroupId = (int)($question->group_id ?? 0);
		$qGroupName = ($qGroupId > 0 && isset($groupsById[$qGroupId])) ? (string)$groupsById[$qGroupId]->title : '';
?>
	<div class="tableRow<?= $isInherited ? ' formQuestionInherited' : '' ?>">
<?php	if ($isInherited) { ?>
		<div class="tableCell"><?= htmlspecialchars((string)$question->type, ENT_QUOTES, 'UTF-8') ?> <span class="formQuestionInheritedBadge" title="Copied from a previously published version. Edit a new question instead, or delete this one from the draft.">inherited</span></div>
		<div class="tableCell"><?= htmlspecialchars(strip_tags((string)$question->text), ENT_QUOTES, 'UTF-8') ?></div>
		<div class="tableCell"><?= htmlspecialchars((string)$question->prompt, ENT_QUOTES, 'UTF-8') ?></div>
		<div class="tableCell"><?= ! empty($question->required) ? 'Yes' : 'No' ?></div>
		<div class="tableCell"><?= $qGroupName !== '' ? htmlspecialchars($qGroupName, ENT_QUOTES, 'UTF-8') : '<em>Ungrouped</em>' ?></div>
		<div class="tableCell"><?= (int)$question->sort_order ?></div>
		<div class="tableCell">
<?php		if ($showChoices) {
			$opts = $question->options();
			if (empty($opts)) { ?>
			<span class="formChoiceNa">&mdash;</span>
<?php		} else { ?>
			<ul class="formChoiceListReadonly">
<?php			foreach ($opts as $opt) { ?>
				<li><?= htmlspecialchars((string)$opt->text, ENT_QUOTES, 'UTF-8') ?> <span class="formChoiceValueReadonly">(<?= htmlspecialchars((string)$opt->value, ENT_QUOTES, 'UTF-8') ?>)</span></li>
<?php			} ?>
			</ul>
<?php		}
		} else { ?>
			<span class="formChoiceNa">&mdash;</span>
<?php	} ?>
		</div>
		<div class="tableCell"><label><input type="checkbox" name="question_delete[<?= (int)$question->id ?>]" value="1" title="Remove this question from this draft" /> remove</label></div>
<?php	} else {
		$selType = $postArrScalar('type', (int)$question->id, (string)$question->type);
		$selGroup = $postArrScalar('group_id', (int)$question->id, (string)(int)$question->group_id);
?>
		<div class="tableCell">
			<select name="type[<?=$question->id?>]">
				<option value="text"<?php if ($selType === "text") print " selected";?>>Text</option>
				<option value="textarea"<?php if ($selType === "textarea") print " selected";?>>Textarea</option>
				<option value="select"<?php if ($selType === "select") print " selected";?>>Select</option>
				<option value="checkbox"<?php if ($selType === "checkbox") print " selected";?>>Checkbox</option>
				<option value="radio"<?php if ($selType === "radio") print " selected";?>>Radio</option>
				<option value="submit"<?php if ($selType === "submit") print " selected";?>>Submit</option>
				<option value="hidden"<?php if ($selType === "hidden") print " selected";?>>Hidden</option>
			</select>
		</div>
		<div class="tableCell"><input type="text" name="text[<?=$question->id?>]" value="<?= $h($postArrScalar('text', (int)$question->id, strip_tags((string)$question->text))) ?>" /></div>
		<div class="tableCell"><input type="text" name="prompt[<?=$question->id?>]" value="<?= $h($postArrScalar('prompt', (int)$question->id, $question->prompt ?? '')) ?>" /></div>
		<div class="tableCell"><input type="checkbox" name="required[<?=$question->id?>]" value="1"<?php if ($postArrCheckbox('required', (int)$question->id, ! empty($question->required))) print " checked";?> /></div>
		<div class="tableCell">
			<select name="group_id[<?=$question->id?>]">
				<option value=""<?php if ($selGroup === '' || $selGroup === '0') print ' selected'; ?>>Ungrouped</option>
<?php foreach ($groups as $group) { ?>
				<option value="<?= (int)$group->id ?>"<?php if ((int)$selGroup === (int)$group->id && (int)$group->id > 0) print ' selected'; ?>><?= htmlspecialchars((string)$group->title, ENT_QUOTES, 'UTF-8') ?></option>
<?php } ?>
			</select>
		</div>
		<div class="tableCell"><input type="number" name="sort_order[<?=$question->id?>]" value="<?= (int)$postArrScalar('sort_order', (int)$question->id, (int)$question->sort_order) ?>" /></div>
		<div class="tableCell">
<?php	if ($showChoices) { ?>
			<div class="formQuestionChoices">
				<table class="formChoiceTable">
					<thead>
					<tr><th scope="col">Label</th><th scope="col">Value</th><th scope="col">Order</th><th scope="col">Del</th></tr>
					</thead>
					<tbody>
<?php		foreach ($question->options() as $opt) { ?>
					<tr>
						<td><input type="text" name="option_text[<?= (int)$opt->id ?>]" value="<?= $h($postArrScalar('option_text', (int)$opt->id, $opt->text ?? '')) ?>" size="18" maxlength="128" /></td>
						<td><input type="text" name="option_value[<?= (int)$opt->id ?>]" value="<?= $h($postArrScalar('option_value', (int)$opt->id, $opt->value ?? '')) ?>" size="14" maxlength="128" /></td>
						<td><input type="number" name="option_sort_order[<?= (int)$opt->id ?>]" value="<?= (int)$postArrScalar('option_sort_order', (int)$opt->id, (int)$opt->sort_order) ?>" /></td>
						<td><input type="checkbox" name="option_delete[<?= (int)$opt->id ?>]" value="1" title="Remove this choice"<?php if ($postArrCheckbox('option_delete', (int)$opt->id, false)) print ' checked'; ?> /></td>
					</tr>
<?php		} ?>
					</tbody>
					<tbody class="formChoiceNewRows" data-question-id="<?= (int)$question->id ?>">
<?php
					$newTexts = $postArrList('option_new_text', (int)$question->id);
					$newVals = $postArrList('option_new_value', (int)$question->id);
					$newRowCount = max(count($newTexts), count($newVals), 1);
					for ($_i = 0; $_i < $newRowCount; $_i++) {
						$nt = $newTexts[$_i] ?? '';
						$nv = $newVals[$_i] ?? '';
?>
					<tr class="formChoiceNewRow">
						<td><input type="text" name="option_new_text[<?= (int)$question->id ?>][]" value="<?= $h($nt) ?>" placeholder="Label" size="18" maxlength="128" autocomplete="off" /></td>
						<td><input type="text" name="option_new_value[<?= (int)$question->id ?>][]" value="<?= $h($nv) ?>" placeholder="Value" size="14" maxlength="128" autocomplete="off" /></td>
						<td></td>
						<td><button type="button" class="formChoiceRemoveNewRow" title="Remove this row">&times;</button></td>
					</tr>
<?php				} ?>
					</tbody>
				</table>
				<p><button type="button" class="formChoiceAddRow" data-question-id="<?= (int)$question->id ?>">Add choice row</button></p>
			</div>
<?php	} else { ?>
			<span class="formChoiceNa">—</span>
<?php	} ?>
		</div>
		<div class="tableCell"><label><input type="checkbox" name="question_delete[<?= (int)$question->id ?>]" value="1" title="Remove this question from this draft" /> remove</label></div>
<?php	} ?>
	</div>
<?php	} ?>
<?php
	$selTypeNew = $postScalar('type_new', 'text');
	$selGroupNew = $postScalar('group_id_new', '');
?>
	<div class="tableRow">
		<div class="tableCell">
			<select name="type_new">
				<option value="text"<?php if ($selTypeNew === 'text') print ' selected'; ?>>Text</option>
				<option value="textarea"<?php if ($selTypeNew === 'textarea') print ' selected'; ?>>Textarea</option>
				<option value="select"<?php if ($selTypeNew === 'select') print ' selected'; ?>>Select</option>
				<option value="checkbox"<?php if ($selTypeNew === 'checkbox') print ' selected'; ?>>Checkbox</option>
				<option value="radio"<?php if ($selTypeNew === 'radio') print ' selected'; ?>>Radio</option>
				<option value="hidden"<?php if ($selTypeNew === 'hidden') print ' selected'; ?>>Hidden</option>
			</select>
		</div>
		<div class="tableCell"><input type="text" name="text_new" value="<?= $h($postScalar('text_new', '')) ?>" /></div>
		<div class="tableCell"><input type="text" name="prompt_new" value="<?= $h($postScalar('prompt_new', '')) ?>" /></div>
		<div class="tableCell"><input type="checkbox" name="required_new" value="1"<?php if ($postCheckbox('required_new', false)) print ' checked'; ?> /></div>
		<div class="tableCell">
			<select name="group_id_new">
				<option value=""<?php if ($selGroupNew === '' || $selGroupNew === '0') print ' selected'; ?>>Ungrouped</option>
<?php foreach ($groups as $group) { ?>
				<option value="<?= (int)$group->id ?>"<?php if ((int)$selGroupNew === (int)$group->id && (int)$group->id > 0) print ' selected'; ?>><?= htmlspecialchars((string)$group->title, ENT_QUOTES, 'UTF-8') ?></option>
<?php } ?>
			</select>
		</div>
		<div class="tableCell"><input type="number" name="sort_order_new" value="<?= (int)$postScalar('sort_order_new', '50') ?>" /></div>
		<div class="tableCell"><span class="formChoiceNa" title="Save the new question first, then add choices.">—</span></div>
		<div class="tableCell"></div>
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
<?php } ?>