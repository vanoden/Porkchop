<div class="title">Repository <?=$repository->code?></div>
<?php	 if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?php	 }
    if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?php	 } ?>
<form name="repositoryForm" action="/_storage/repository" method="post">
<input type="hidden" name="id" value="<?=$repository->id?>" />
<div class="container">
    <span class="label">Name</span>
    <input type="text" name="name" class="value input wide_xl" value="<?=$form['name']?>" />
</div>
<div class="container">
    <span class="label">Type</span>
<?php	 if ($repository->id) { ?>
    <span class="value"><?=$repository->type?></span>
<?php	 } else { ?>
    <select name="type" class="value input wide_xl">
        <option value="Local"<?php	if ($form['type'] == "local") print " selected"; ?>>Local</option>
        <option value="S3"<?php	if ($form['type'] == "S3") print " selected"; ?>>Amazon S3</option>
        <option value="Drive"<?php	if ($form['type'] == "Drive") print " selected"; ?>>Google Drive</option>
        <option value="DropBox"<?php	if ($form['type'] == "DropBox") print " selected"; ?>>DropBox</option>
    </select>
<?php	 } ?>
</div>
<div class="container">
    <span class="label">Status</span>
    <select name="status" class="value input wide_xl">
        <option value="NEW"<?php	if ($form['status'] == "NEW") print " selected"; ?>>NEW</option>
        <option value="ACTIVE"<?php	if ($form['status'] == "ACTIVE") print " selected"; ?>>ACTIVE</option>
        <option value="DISABLED"<?php	if ($form['status'] == "DISABLED") print " selected"; ?>>DISABLED</option>
    </select>
</div>
<div class="container">
    <span class="label">Path</span>
    <input type="text" name="path" class="value input wide_xl" value="<?=$form['path']?>" />
</div>
<div class="container">
    <span class="label">Endpoint</span>
    <input type="text" name="endpoint" class="value input wide_xl" value="<?=$form['endpoint']?>" />
</div>
<div class="form_footer">
    <input type="submit" name="btn_submit" class="button" value="Update" />
    <input type="button" name="btn_files" class="button" value="Browse" onclick="window.location.href='/_storage/browse?code=<?=$repository->code?>';" />
    <input type="button" name="btn_back" class="button" value="Back" onclick="window.location.href='/_storage/repositories';" />
</div>
</form>
<?php	if ($repository->id) { ?>
<form name="repoUpload" action="/_storage/file" method="post" enctype="multipart/form-data">
<div class="container">
	<span class="label">Upload File</span>
	<input type="hidden" name="repository_id" value="<?=$repository->id?>" />
	<input type="file" name="uploadFile" />
	<input type="submit" name="btn_submit" class="button" value="Upload" />
</div>
</form>
<?php	} ?>
