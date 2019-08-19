<div class="title">Edit Page Parameters</div>
<?	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	} ?>
<div class="container_narrow">
	<span class="label">Module</span><span><?=$module?></span>
</div>
<div class="container_narrow">
	<span class="label">View</span><span><?=$view?></span>
</div>
<div class="container_narrow">
	<span class="label">Index</span><span><?=$index?></span>
</div>
<div class="subheading">Metadata</div>
<div class="table">
	<div class="tableHead">
		<div class="tableCell">Key</div>
		<div class="tableCell">Value</div>
	</div>
<?	foreach ($page->metadata as $key=>$value) { ?>
	<div class="tableRow">
		<div class="tableCell"><?=$key?></div>
		<div class="tableCell"><input type="text" name="metadata[<?=$key?>]" value="<?=$value?>" /></div>
		<div class="tableCell"><input type="button" name="add[<?=$key?>]" value="<?=$value?>" /></div>
	</div>
<?	} ?>
</div>
