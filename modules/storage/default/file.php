<?	if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	} elseif ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?	} ?>
<form name="fileForm" action="/_storage/file" method="post">
<input type="hidden" name="id" value="<?=$file->id?>">
<div class="title">File Details</div>
<div class="container fileDetailContainer">
	<span class="label">Code</span>
	<span class="value"><?=$file->code?>
</div>
<div class="container fileDetailContainer">
	<span class="label">Display Name</span>
	<input type="text" class="value input" name="display_name" value="<?=$file->display_name?>" />
</div>
<div class="container fileDetailContainer">
	<span class="label">Repository</span>
	<span class="value"><?=$file->repository->name?></span>
</div>
<div class="container fileDetailContainer">
	<span class="label">Name</span>
	<input type="text" class="value input" name="name" value="<?=$file->name()?>" />
</div>
<div class="container fileDetailContainer">
	<span class="label">Path</span>
	<input class="value input" name="path" type="text" value="<?=$file->path()?>" />
</div>
<div class="container fileDetailContainer">
	<span class="label">Size</span>
	<span class="value"><?=$file->size?></span>
</div>
<div class="container fileDetailContainer">
	<span class="label">Mime-Type</span>
	<span class="value"><?=$file->mime_type?></span>
</div>
<div class="container fileDetailContainer">
	<span class="label">Date</span>
	<span class="value"><?=$file->date_created?></span>
</div>
<div class="container fileDetailContainer">
	<span class="label">Owner</span>
	<span class="value"><?=$file->user->login?></span>
</div>
<div class="container fileDetailContainer">
	<span class="label">Download URI</span>
	<span class="value"><?=$file->downloadURI()?></span>
</div>
<div class="form_footer">
	<input type="submit" class="button" name="btn_submit" value="Update"/>
	<input type="submit" class="button" name="btn_submit" value="Download"/>
</div>
</form>
