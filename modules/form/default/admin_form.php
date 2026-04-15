<?= $page->showAdminPageInfo(); ?>

<form method="post">
<input type="hidden" name="id" value="<?=$form->id?>" />
<input type="hidden" name="csrf_token" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>" />

<label for="code">Code</label>
<input type="text" name="code" value="<?=$form->code?>" />

<label for="title">Title</label>
<input type="text" name="title" value="<?=$form->title?>" />

<label for="description">Description</label>
<input type="text" name="description" value="<?=strip_tags($form->description)?>" />

<label for="action">Action</label>
<input type="text" name="action" value="<?=$form->action?>" />

<label for="method">Method</label>
<select name="method">
	<option value="post"<?php if ($form->method == "post") print " selected";?>>POST</option>
	<option value="get"<?php if ($form->method == "get") print " selected";?>>GET</option>
</select>

<div class="section">
	<input type="submit" name="submit" value="Save" />
</div>

<?php	if ($form->exists()) { ?>
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Version</div>
		<div class="tableCell">Activated On</div>
		<div class="tableCell">Activated By</div>
	</div>
<?php	if (empty($versions)) { ?>
	<div class="tableRow">
		<div class="tableCell">No versions yet. Use “Add New Version” below.</div>
		<div class="tableCell">—</div>
		<div class="tableCell">—</div>
	</div>
<?php	} else { foreach ($versions as $version) { ?>
	<div class="tableRow">
		<div class="tableCell">
			<a href="/_form/admin_version/<?=$version->id?>"><?=htmlspecialchars($version->name)?></a>
			<?php if ($version->active()) print " <em>(Published)</em>"; ?>
		</div>
		<div class="tableCell"><?= $version->date_activated ? htmlspecialchars((string)$version->date_activated) : '—' ?></div>
		<div class="tableCell"><?= $version->user_id_activated ? htmlspecialchars($version->activatedByDisplayName(), ENT_QUOTES, 'UTF-8') : '—' ?></div>
	</div>
<?php	} } ?>
</div>
<div class="section">
	<a href="/_form/admin_version/<?=$form->code?>">Add New Version</a>
</div>
<?php } ?>
</form>