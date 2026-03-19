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

<label for="instructions">Instructions</label>
<textarea name="instructions"><?=$form->instructions?></textarea>

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
<?php	foreach($versions as $version) { ?>
	<div class="tableRow">
		<div class="tableCell">
			<a href="/_form/admin_version/<?=$version->id?>"><?=$version->name?></a>
			<?php if ($version->active) print " (Active)"; ?>
		</div>
		<div class="tableCell"><?=$version->date_activated?></div>
		<div class="tableCell"><?=$version->activated_by?></div>
	</div>
<?php	} ?>
</div>
<div class="section">
	<a href="/_form/admin_version/<?=$form->code?>">Add New Version</a>
</div>
<?php } ?>
</form>