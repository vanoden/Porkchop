<div class="title">Edit Page Parameters</div>
<?	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	} ?>
<div class="container_narrow">
	<span class="label">Module</span><span><?=$page->module?></span>
</div>
<div class="container_narrow">
	<span class="label">View</span><span><?=$page->view?></span>
</div>
<div class="container_narrow">
	<span class="label">Index</span><span><?=$page->index?></span>
</div>
<div class="subheading">Metadata</div>
<?	foreach ($metadata as $key=>$value) { ?>
<div class="container">
	<span class="label"><?=$key?></span>
	<input type="text" name="page_<?=$key?>" value="<?=$value?>" />
</div>
<?	} ?>
