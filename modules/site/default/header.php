<?=$page->showAdminPageInfo()?>
<form method="post" action="/_site/header">
<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>"/>
<input type="hidden" name="id" value="<?=$header->id?>"/>
<div>
	<span class="label">HTTP Header Name</span>
	<input type="text" style="width: 200px" name="name" class="value input" value="<?=$header->name()?>" />
</div>
<div>
	<span class="label">Contents</span>
	<textarea name="value" style="width: 100%" class="value input"><?=$header->value()?></textarea>
</div>
<input type="submit" name="btn_submit" class="button" value="Submit" />
</form>