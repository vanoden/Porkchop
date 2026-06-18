<?= $page->showAdminPageInfo(); ?>

<?php if (isset($version) && $version->exists() && isset($form) && $form->exists()) { ?>
<div class="formVersionStatusPanels">
<?php if (! $isViewingLiveVersion) { ?>
	<section class="formVersionPanel formVersionPanel--draft" aria-labelledby="formVersionDraftHeading">
		<div class="formVersionPanelHeader">
			<span class="formVersionPanelBadge formVersionPanelBadge--draft"><?= $versionWasPublished ? 'Not live' : 'Draft' ?></span>
			<h4 class="formVersionPanelTitle" id="formVersionDraftHeading">
				<?= $versionWasPublished ? 'Not the live version' : 'Draft — not visible to visitors' ?>
			</h4>
		</div>
		<div class="formVersionPanelBody">
<?php	if ($versionWasPublished) { ?>
			<p class="formVersionPanelLead">This version was published before but visitors no longer see it.</p>
<?php	} elseif ($liveFormUrl !== '') { ?>
			<p class="formVersionPanelLead">You are editing unpublished changes. The live form below is what visitors see today.</p>
<?php	} else { ?>
			<p class="formVersionPanelLead">Nothing is published yet. Publish when this version is ready for visitors.</p>
<?php	} ?>
			<div class="formVersionPanelAction">
				<a class="button btn-secondary formVersionPreviewBtn" href="<?= $h($previewPath) ?>" target="_blank" rel="noopener noreferrer">Preview this version</a>
			</div>
			<p class="formVersionPanelHint">Admin only — preview layout and test submissions before publishing.</p>
		</div>
	</section>
<?php } ?>

<?php if ($isViewingLiveVersion) { ?>
	<section class="formVersionPanel formVersionPanel--published formVersionPanel--isLive" aria-labelledby="formVersionLiveHeading">
		<div class="formVersionPanelHeader">
			<span class="formVersionPanelBadge formVersionPanelBadge--live">Live</span>
			<h4 class="formVersionPanelTitle" id="formVersionLiveHeading">Published — live for visitors</h4>
		</div>
		<div class="formVersionPanelBody">
			<p class="formVersionPanelLead">This version is what visitors see when they open the form.</p>
			<div class="formVersionPanelAction">
				<a class="button formVersionLiveOpenBtn" href="<?= $h($liveFormPath) ?>" target="_blank" rel="noopener noreferrer">Open live form</a>
			</div>
			<div class="formVersionLiveUrl">
				<span class="formVersionLiveUrlLabel">Visitor link</span>
				<div class="formVersionLiveUrlBox">
					<a href="<?= $h($liveFormPath) ?>" class="formVersionLiveUrlLink" target="_blank" rel="noopener noreferrer"><?= $h($liveFormUrl) ?></a>
					<button type="button" class="formVersionCopyLink" data-copy-url="<?= $h($liveFormUrl) ?>" title="Copy link to clipboard" aria-label="Copy link to clipboard">
						<i class="fa fa-clipboard" aria-hidden="true"></i>
					</button>
					<span class="formVersionCopyFeedback" hidden aria-live="polite">Copied!</span>
				</div>
			</div>
		</div>
	</section>
<?php } elseif ($liveFormUrl !== '') { ?>
	<section class="formVersionPanel formVersionPanel--published" aria-labelledby="formVersionLiveHeading">
		<div class="formVersionPanelHeader">
			<span class="formVersionPanelBadge formVersionPanelBadge--live">Live</span>
			<h4 class="formVersionPanelTitle" id="formVersionLiveHeading">Published — live for visitors</h4>
		</div>
		<div class="formVersionPanelBody">
			<p class="formVersionPanelLead">Visitors see <strong><?= $h($liveFormVersion->name ?? 'the published version') ?></strong>, not the draft you are editing above.</p>
			<div class="formVersionPanelAction">
				<a class="button formVersionLiveOpenBtn" href="<?= $h($liveFormPath) ?>" target="_blank" rel="noopener noreferrer">Open live form</a>
			</div>
			<div class="formVersionLiveUrl">
				<span class="formVersionLiveUrlLabel">Visitor link</span>
				<div class="formVersionLiveUrlBox">
					<a href="<?= $h($liveFormPath) ?>" class="formVersionLiveUrlLink" target="_blank" rel="noopener noreferrer"><?= $h($liveFormUrl) ?></a>
					<button type="button" class="formVersionCopyLink" data-copy-url="<?= $h($liveFormUrl) ?>" title="Copy link to clipboard" aria-label="Copy link to clipboard">
						<i class="fa fa-clipboard" aria-hidden="true"></i>
					</button>
					<span class="formVersionCopyFeedback" hidden aria-live="polite">Copied!</span>
				</div>
			</div>
		</div>
	</section>
<?php } ?>
</div>
<?php } ?>

<?php if (! empty($isLocked)) { ?>
<div class="formVersionReadonly monitor-admin-list">

  <ul class="connectBorder pageMessage warningText">
    <li class="pageMessage--warning">Read-only: this version has been published and cannot be edited.<?php
      if ($publishedDate !== '') { ?> Published <?= $h($publishedDate) ?><?php }
      if ($activatedBy !== '') { ?> by <?= $h($activatedBy) ?><?php }
    ?>. To make changes, create a new draft.
    </li>
  </ul>


  <div class="formVersionMeta">
	<div class="formVersionMetaHeader">
		<div>Field</div>
		<div>Value</div>
	</div>
	<div class="formVersionMetaBlock">
		<div class="formVersionMetaField formVersionMetaField--label">Version code</div>
		<div class="formVersionMetaField"><?= $h($version->code) ?></div>
	</div>
	<div class="formVersionMetaBlock">
		<div class="formVersionMetaField formVersionMetaField--label">Version name</div>
		<div class="formVersionMetaField"><?= $h($version->name) ?></div>
	</div>
	<div class="formVersionMetaBlock">
		<div class="formVersionMetaField formVersionMetaField--label">Description</div>
		<div class="formVersionMetaField"><?= $h(strip_tags((string)$version->description)) ?></div>
	</div>
	<div class="formVersionMetaBlock">
		<div class="formVersionMetaField formVersionMetaField--label">Instructions</div>
		<div class="formVersionMetaField"><?= nl2br($h($version->instructions)) ?></div>
	</div>
  </div>

  <h3>Groups</h3>
  <div class="formGroupsEditor formGroupsEditor--readonly">
	<div class="formGroupsEditorHeader">
		<div>Title</div>
		<div>Instructions</div>
		<div>View Order</div>
	</div>
<?php if (empty($groups)) { ?>
	<div class="formGroupBlock formGroupBlock--empty"><em>No groups defined.</em></div>
<?php } else { foreach ($groups as $group) { ?>
	<div class="formGroupBlock">
		<div class="formGroupField formGroupField--title"><?= $h($group->title) ?></div>
		<div class="formGroupField"><?= $h($group->instructions) !== '' ? $h($group->instructions) : '<span class="formReadonlyEmpty">&mdash;</span>' ?></div>
		<div class="formGroupField formGroupField--order"><?= (int)$group->sort_order ?></div>
	</div>
<?php }} ?>
  </div>

  <h3>Questions</h3>
  <div class="formQuestionsEditor formQuestionsEditor--readonly">
	<div class="formQuestionsEditorHeader">
		<div>Type</div>
		<div>Question</div>
		<div>Prompt</div>
		<div>Required</div>
		<div>Group</div>
		<div>View Order</div>
		<div>Choices</div>
	</div>
<?php if (empty($questions)) { ?>
	<div class="formQuestionBlock formQuestionBlock--empty"><em>No questions defined.</em></div>
<?php } else { foreach ($questions as $question) {
	$choiceTypes = array('select', 'radio', 'checkbox');
	$showChoices = in_array($question->type, $choiceTypes, true);
	$gid = (int)($question->group_id ?? 0);
	$groupName = ($gid > 0 && isset($groupsById[$gid])) ? (string)$groupsById[$gid]->title : '';
?>
	<div class="formQuestionBlock">
		<div class="formQuestionField formQuestionField--type"><?= $h($question->type) ?></div>
		<div class="formQuestionField formQuestionField--question"><?= $h(strip_tags((string)$question->text)) ?></div>
		<div class="formQuestionField"><?= $h($question->prompt) ?></div>
		<div class="formQuestionField"><?= ! empty($question->required) ? 'Yes' : 'No' ?></div>
		<div class="formQuestionField"><?= $groupName !== '' ? $h($groupName) : '<em>Ungrouped</em>' ?></div>
		<div class="formQuestionField"><?= (int)$question->sort_order ?></div>
		<div class="formQuestionField formQuestionField--choices">
<?php	if ($showChoices) {
		$opts = $question->options();
		if (empty($opts)) { ?>
			<span class="formChoiceNa">&mdash;</span>
<?php		} else { ?>
			<ul class="formChoiceListReadonly">
<?php			foreach ($opts as $opt) { ?>
				<li><?= $h($opt->text) ?> <span class="formChoiceValueReadonly">(<?= $h($opt->value) ?>)</span></li>
<?php			} ?>
			</ul>
<?php		}
	} else { ?>
			<span class="formChoiceNa">&mdash;</span>
<?php	} ?>
		</div>
	</div>
<?php }} ?>
  </div>

<div class="formVersionFooter formVersionFooter--readonly">
	<div class="filter-bar formVersionFooterBar">
		<div class="filter-bar__controls">
			<p class="formVersionPublicationSummary">Create a new draft to edit this version, or take the form offline.</p>
		</div>
		<div class="button-group filter-bar__actions">
			<a class="button btn-secondary formVersionAction" href="<?= $h($createDraftUrl) ?>">Create new draft from this version</a>
<?php	if ($version->active()) { ?>
			<form method="post" class="formVersionUnpublishForm">
				<input type="hidden" name="csrf_token" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>" />
				<input type="hidden" name="id" value="<?= (int)$version->id ?>" />
				<input type="hidden" name="form_id" value="<?= (int)$form->id ?>" />
				<button type="submit" name="unpublish_form" value="Unpublish form (take offline)" class="btn-secondary" onclick="return confirm('Take this form offline? Visitors will not see a live version until you publish a version again.');">Unpublish form (take offline)</button>
			</form>
<?php	} ?>
		</div>
	</div>
</div>

</div>
<?php } else { ?>

<form method="post" id="formVersionEdit" class="monitor-admin-list form-admin-edit">
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
<div class="formGroupsEditor" id="formGroupsTable">
	<div class="formGroupsEditorHeader">
		<div>Title</div>
		<div>Instructions</div>
		<div>View Order</div>
	</div>
<?php foreach ($groups as $group) { ?>
	<div class="formGroupBlock">
		<div class="formGroupField formGroupField--title">
			<input type="text" name="group_title[<?= (int)$group->id ?>]" value="<?= $h($postArrScalar('group_title', (int)$group->id, $group->title ?? '')) ?>" placeholder="Group title" />
		</div>
		<div class="formGroupField">
			<input type="text" name="group_instructions[<?= (int)$group->id ?>]" value="<?= $h($postArrScalar('group_instructions', (int)$group->id, $group->instructions ?? '')) ?>" placeholder="Optional instructions shown above grouped questions" />
		</div>
		<div class="formGroupField formGroupField--order">
			<input type="number" name="group_sort_order[<?= (int)$group->id ?>]" value="<?= (int)$postArrScalar('group_sort_order', (int)$group->id, (int)$group->sort_order) ?>" />
		</div>
	</div>
<?php } ?>
	<div class="formGroupBlock formGroupNewRow">
		<div class="formGroupField formGroupField--title">
			<input type="text" name="group_title_new" value="<?= $h($postScalar('group_title_new', '')) ?>" placeholder="New group title" autocomplete="off" />
		</div>
		<div class="formGroupField">
			<input type="text" name="group_instructions_new" value="<?= $h($postScalar('group_instructions_new', '')) ?>" placeholder="Optional instructions" autocomplete="off" />
		</div>
		<div class="formGroupField formGroupField--order">
			<input type="number" name="group_sort_order_new" value="<?= (int)$postScalar('group_sort_order_new', '50') ?>" />
		</div>
	</div>
</div>

<h3>Questions</h3>
<div class="formQuestionsEditor" id="formQuestionsTable">
	<div class="formQuestionsEditorHeader">
		<div>Type</div>
		<div>Question</div>
		<div>Prompt</div>
		<div>Required</div>
		<div>Group</div>
		<div>View Order</div>
		<div>Choices</div>
		<div>Delete</div>
	</div>
<?php	foreach($questions as $question) {
		$choiceTypes = array('select', 'radio', 'checkbox');
		$showChoices = in_array($question->type, $choiceTypes, true);
		$isInherited = ! empty($inheritedKeys[(string)$question->aggregate_key]);
		$qGroupId = (int)($question->group_id ?? 0);
		$qGroupName = ($qGroupId > 0 && isset($groupsById[$qGroupId])) ? (string)$groupsById[$qGroupId]->title : '';
?>
	<div class="formQuestionBlock<?= $isInherited ? ' formQuestionBlock--inherited' : ' formQuestionEditableRow' ?>">
		<div class="formQuestionMainRow">
<?php	if ($isInherited) { ?>
		<div class="formQuestionField formQuestionField--type"><?= htmlspecialchars((string)$question->type, ENT_QUOTES, 'UTF-8') ?> <span class="formQuestionInheritedBadge" title="Copied from a previously published version. Edit a new question instead, or delete this one from the draft.">inherited</span></div>
		<div class="formQuestionField formQuestionField--question"><?= htmlspecialchars(strip_tags((string)$question->text), ENT_QUOTES, 'UTF-8') ?></div>
		<div class="formQuestionField"><?= htmlspecialchars((string)$question->prompt, ENT_QUOTES, 'UTF-8') ?></div>
		<div class="formQuestionField"><?= ! empty($question->required) ? 'Yes' : 'No' ?></div>
		<div class="formQuestionField"><?= $qGroupName !== '' ? htmlspecialchars($qGroupName, ENT_QUOTES, 'UTF-8') : '<em>Ungrouped</em>' ?></div>
		<div class="formQuestionField"><?= (int)$question->sort_order ?></div>
		<div class="formQuestionField formQuestionField--choices">
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
		<div class="formQuestionField formQuestionField--delete"><label><input type="checkbox" name="question_delete[<?= (int)$question->id ?>]" value="1" title="Remove this question from this draft" /> remove</label></div>
		</div>
<?php	} else {
		$selType = $postArrScalar('type', (int)$question->id, (string)$question->type);
		$selGroup = $postArrScalar('group_id', (int)$question->id, (string)(int)$question->group_id);
		$showChoices = in_array($selType, $choiceTypes, true);
?>
		<div class="formQuestionField formQuestionField--type">
			<select name="type[<?=$question->id?>]" class="formQuestionType">
				<option value="text"<?php if ($selType === "text") print " selected";?>>Text</option>
				<option value="textarea"<?php if ($selType === "textarea") print " selected";?>>Textarea</option>
				<option value="select"<?php if ($selType === "select") print " selected";?>>Select</option>
				<option value="checkbox"<?php if ($selType === "checkbox") print " selected";?>>Checkbox</option>
				<option value="radio"<?php if ($selType === "radio") print " selected";?>>Radio</option>
				<option value="submit"<?php if ($selType === "submit") print " selected";?>>Submit</option>
				<option value="hidden"<?php if ($selType === "hidden") print " selected";?>>Hidden</option>
			</select>
		</div>
		<div class="formQuestionField formQuestionField--question">
			<input type="text" name="text[<?=$question->id?>]" value="<?= $h($postArrScalar('text', (int)$question->id, strip_tags((string)$question->text))) ?>" placeholder="Field name / key" />
		</div>
		<div class="formQuestionField"><input type="text" name="prompt[<?=$question->id?>]" value="<?= $h($postArrScalar('prompt', (int)$question->id, $question->prompt ?? '')) ?>" placeholder="Label shown to user" /></div>
		<div class="formQuestionField"><input type="checkbox" name="required[<?=$question->id?>]" value="1"<?php if ($postArrCheckbox('required', (int)$question->id, ! empty($question->required))) print " checked";?> /></div>
		<div class="formQuestionField">
			<select name="group_id[<?=$question->id?>]">
				<option value=""<?php if ($selGroup === '' || $selGroup === '0') print ' selected'; ?>>Ungrouped</option>
<?php foreach ($groups as $group) { ?>
				<option value="<?= (int)$group->id ?>"<?php if ((int)$selGroup === (int)$group->id && (int)$group->id > 0) print ' selected'; ?>><?= htmlspecialchars((string)$group->title, ENT_QUOTES, 'UTF-8') ?></option>
<?php } ?>
			</select>
		</div>
		<div class="formQuestionField"><input type="number" name="sort_order[<?=$question->id?>]" value="<?= (int)$postArrScalar('sort_order', (int)$question->id, (int)$question->sort_order) ?>" /></div>
		<div class="formQuestionField formQuestionField--choices"><span class="formChoiceNa">—</span></div>
		<div class="formQuestionField formQuestionField--delete"><label><input type="checkbox" name="question_delete[<?= (int)$question->id ?>]" value="1" title="Remove this question from this draft" /> remove</label></div>
		</div>
<?php	if ($showChoices) { ?>
		<div class="formQuestionChoicesSubrow formQuestionChoicesEditor" data-question-id="<?= (int)$question->id ?>">
				<p class="formQuestionChoicesLabel">Choices</p>
				<table class="formChoiceTable">
					<thead>
					<tr><th scope="col">Label</th><th scope="col">Value</th><th scope="col">Order</th><th scope="col">Del</th></tr>
					</thead>
					<tbody>
<?php		foreach ($question->options() as $opt) { ?>
					<tr>
						<td><input type="text" name="option_text[<?= (int)$opt->id ?>]" value="<?= $h($postArrScalar('option_text', (int)$opt->id, $opt->text ?? '')) ?>" maxlength="128" /></td>
						<td><input type="text" name="option_value[<?= (int)$opt->id ?>]" value="<?= $h($postArrScalar('option_value', (int)$opt->id, $opt->value ?? '')) ?>" maxlength="128" /></td>
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
						<td><input type="text" name="option_new_text[<?= (int)$question->id ?>][]" value="<?= $h($nt) ?>" placeholder="Label" maxlength="128" autocomplete="off" /></td>
						<td><input type="text" name="option_new_value[<?= (int)$question->id ?>][]" value="<?= $h($nv) ?>" placeholder="Value" maxlength="128" autocomplete="off" /></td>
						<td></td>
						<td><button type="button" class="formChoiceRemoveNewRow" title="Remove this row">&times;</button></td>
					</tr>
<?php				} ?>
					</tbody>
				</table>
				<p><button type="button" class="formChoiceAddRow" data-question-id="<?= (int)$question->id ?>">Add choice</button></p>
			</div>
<?php	} else { ?>
		<div class="formQuestionChoicesSubrow formQuestionChoicesEditor" data-question-id="<?= (int)$question->id ?>" hidden>
			<p class="formQuestionChoicesLabel">Choices</p>
			<table class="formChoiceTable">
				<thead>
				<tr><th scope="col">Label</th><th scope="col">Value</th><th scope="col">Order</th><th scope="col">Del</th></tr>
				</thead>
				<tbody></tbody>
				<tbody class="formChoiceNewRows" data-question-id="<?= (int)$question->id ?>">
				<tr class="formChoiceNewRow">
					<td><input type="text" name="option_new_text[<?= (int)$question->id ?>][]" value="" placeholder="Label" maxlength="128" autocomplete="off" /></td>
					<td><input type="text" name="option_new_value[<?= (int)$question->id ?>][]" value="" placeholder="Value" maxlength="128" autocomplete="off" /></td>
					<td></td>
					<td><button type="button" class="formChoiceRemoveNewRow" title="Remove this row">&times;</button></td>
				</tr>
				</tbody>
			</table>
			<p><button type="button" class="formChoiceAddRow" data-question-id="<?= (int)$question->id ?>">Add choice</button></p>
		</div>
<?php	} ?>
<?php	} ?>
	</div>
<?php	}
	$newTypes = $postIndexedList('type_new');
	$newTexts = $postIndexedList('text_new');
	$newPrompts = $postIndexedList('prompt_new');
	$newGroups = $postIndexedList('group_id_new');
	$newSortOrders = $postIndexedList('sort_order_new');
	$newRequired = $postIndexedList('required_new');
	$indexKeys = array_unique(array_merge(
		array_keys($newTypes),
		array_keys($newTexts),
		array_keys($newPrompts),
		array_keys($newGroups),
		array_keys($newSortOrders)
	));
	$newRowCount = ($hasErrors && $indexKeys) ? (max($indexKeys) + 1) : 1;
	$defaultNewSort = 50;
	foreach ($questions as $q) {
		$defaultNewSort = max($defaultNewSort, (int)$q->sort_order + 10);
	}
?>
<?php for ($ni = 0; $ni < $newRowCount; $ni++) {
		$selTypeNew = (string)($newTypes[$ni] ?? 'text');
		$selGroupNew = (string)($newGroups[$ni] ?? '');
		$choiceTypesNew = array('select', 'radio', 'checkbox');
		$showChoicesNew = in_array($selTypeNew, $choiceTypesNew, true);
		$newOptTexts = ($hasErrors && isset($_POST['option_new_text_new'][$ni]) && is_array($_POST['option_new_text_new'][$ni]))
			? array_map('strval', $_POST['option_new_text_new'][$ni]) : array();
		$newOptVals = ($hasErrors && isset($_POST['option_new_value_new'][$ni]) && is_array($_POST['option_new_value_new'][$ni]))
			? array_map('strval', $_POST['option_new_value_new'][$ni]) : array();
		$newOptRowCount = max(count($newOptTexts), count($newOptVals), 1);
?>
	<div class="formQuestionBlock formQuestionNewRow" data-new-index="<?= (int)$ni ?>">
		<div class="formQuestionMainRow">
		<div class="formQuestionField formQuestionField--type">
			<select name="type_new[<?= (int)$ni ?>]" class="formQuestionNewType">
				<option value="text"<?php if ($selTypeNew === 'text') print ' selected'; ?>>Text</option>
				<option value="textarea"<?php if ($selTypeNew === 'textarea') print ' selected'; ?>>Textarea</option>
				<option value="select"<?php if ($selTypeNew === 'select') print ' selected'; ?>>Select</option>
				<option value="checkbox"<?php if ($selTypeNew === 'checkbox') print ' selected'; ?>>Checkbox</option>
				<option value="radio"<?php if ($selTypeNew === 'radio') print ' selected'; ?>>Radio</option>
				<option value="hidden"<?php if ($selTypeNew === 'hidden') print ' selected'; ?>>Hidden</option>
				<option value="submit"<?php if ($selTypeNew === 'submit') print ' selected'; ?>>Submit</option>
			</select>
		</div>
		<div class="formQuestionField formQuestionField--question">
			<input type="text" name="text_new[<?= (int)$ni ?>]" value="<?= $h($newTexts[$ni] ?? '') ?>" placeholder="Field name / key" autocomplete="off" />
		</div>
		<div class="formQuestionField"><input type="text" name="prompt_new[<?= (int)$ni ?>]" value="<?= $h($newPrompts[$ni] ?? '') ?>" placeholder="Label shown to user" autocomplete="off" /></div>
		<div class="formQuestionField"><input type="checkbox" name="required_new[<?= (int)$ni ?>]" value="1"<?php
			if ($hasErrors && ! empty($newRequired[$ni])) print ' checked';
		?> /></div>
		<div class="formQuestionField">
			<select name="group_id_new[<?= (int)$ni ?>]">
				<option value=""<?php if ($selGroupNew === '' || $selGroupNew === '0') print ' selected'; ?>>Ungrouped</option>
<?php foreach ($groups as $group) { ?>
				<option value="<?= (int)$group->id ?>"<?php if ((int)$selGroupNew === (int)$group->id && (int)$group->id > 0) print ' selected'; ?>><?= htmlspecialchars((string)$group->title, ENT_QUOTES, 'UTF-8') ?></option>
<?php } ?>
			</select>
		</div>
		<div class="formQuestionField"><input type="number" name="sort_order_new[<?= (int)$ni ?>]" class="formQuestionNewSort" value="<?= (int)($newSortOrders[$ni] ?? ($defaultNewSort + ($ni * 10))) ?>" /></div>
		<div class="formQuestionField formQuestionField--choices"><span class="formChoiceNa">—</span></div>
		<div class="formQuestionField formQuestionField--delete"><button type="button" class="formQuestionRemoveNewRow" title="Remove this question row">&times;</button></div>
		</div>
		<div class="formQuestionChoicesSubrow formQuestionChoicesEditor formQuestionNewChoices"<?php if (! $showChoicesNew) print ' hidden'; ?>>
			<p class="formQuestionChoicesLabel">Choices</p>
			<table class="formChoiceTable">
				<thead>
				<tr><th scope="col">Label</th><th scope="col">Value</th><th scope="col"></th></tr>
				</thead>
				<tbody class="formChoiceNewRows" data-new-question-index="<?= (int)$ni ?>">
<?php for ($oi = 0; $oi < $newOptRowCount; $oi++) {
		$nt = $newOptTexts[$oi] ?? '';
		$nv = $newOptVals[$oi] ?? '';
?>
				<tr class="formChoiceNewRow">
					<td><input type="text" name="option_new_text_new[<?= (int)$ni ?>][]" value="<?= $h($nt) ?>" placeholder="Label" maxlength="128" autocomplete="off" /></td>
					<td><input type="text" name="option_new_value_new[<?= (int)$ni ?>][]" value="<?= $h($nv) ?>" placeholder="Value" maxlength="128" autocomplete="off" /></td>
					<td><button type="button" class="formChoiceRemoveNewRow" title="Remove this row">&times;</button></td>
				</tr>
<?php } ?>
				</tbody>
			</table>
			<p><button type="button" class="formChoiceAddRowNewQuestion" data-new-question-index="<?= (int)$ni ?>">Add choice</button></p>
		</div>
	</div>
<?php } ?>
</div>

<div class="formVersionFooter">
	<div class="filter-bar formVersionFooterBar">
		<div class="filter-bar__controls">
			<p class="formQuestionNewHint">Complete every new question row you start, or clear it before saving. Empty rows are ignored.</p>
		</div>
		<div class="button-group filter-bar__actions">
			<input type="button" class="button btn-secondary formQuestionAddRow" value="+ Add question" title="Add another new question row" />
			<button type="submit" name="submit" value="Save">Save</button>
		</div>
	</div>
<?php	if (isset($version) && $version->exists()) { ?>
	<div class="filter-bar formVersionFooterBar formVersionFooterBar--publication">
		<div class="filter-bar__controls">
			<div class="formVersionPublicationSummary">
				<span class="formVersionPublicationLabel">Publication</span>
				<span class="formVersionPublicationStatus">
<?php	if ($version->active()) { ?>
					This version is live (visitors see it when the form is embedded or linked).
<?php	} else { ?>
					Draft — not the live version.
<?php	} ?>
				</span>
			</div>
		</div>
		<div class="button-group filter-bar__actions">
<?php	if (! $version->active()) { ?>
			<button type="submit" name="publish_version" value="Publish this version">Publish this version</button>
<?php	} ?>
<?php	if (! empty($form->active_version_id)) { ?>
			<button type="submit" name="unpublish_form" value="Unpublish form (take offline)" class="btn-secondary" onclick="return confirm('Take this form offline? Visitors will not see a live version until you publish a version again.');">Unpublish form (take offline)</button>
<?php	} ?>
		</div>
	</div>
<?php	} ?>
</div>
</form>
<?php } ?>

<script src="/js/form.admin_version.js"></script>