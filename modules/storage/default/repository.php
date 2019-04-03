<div class="title">Repository <?=$repository->code?></div>
<?  if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?  }
    if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?  } ?>
<form name="repositoryForm" action="/_storage/repository" method="post">
<input type="hidden" name="id" value="<?=$repository->id?>" />
<div class="container">
    <span class="label">Name</span>
    <input type="text" name="name" class="value input wide_xl" value="<?=$form['name']?>" />
</div>
<div class="container">
    <span class="label">Type</span>
<?  if ($repository->id) { ?>
    <span class="value"><?=$repository->type?></span>
<?  } else { ?>
    <select name="type" class="value input wide_xl">
        <option value="Local"<? if ($form['type'] == "local") print " selected"; ?>>Local</option>
        <option value="S3"<? if ($form['type'] == "S3") print " selected"; ?>>Amazon S3</option>
        <option value="Drive"<? if ($form['type'] == "Drive") print " selected"; ?>>Google Drive</option>
        <option value="DropBox"<? if ($form['type'] == "DropBox") print " selected"; ?>>DropBox</option>
    </select>
<?  } ?>
</div>
<div class="container">
    <span class="label">Status</span>
    <select name="status" class="value input wide_xl">
        <option value="NEW"<? if ($form['status'] == "NEW") print " selected"; ?>>NEW</option>
        <option value="ACTIVE"<? if ($form['status'] == "ACTIVE") print " selected"; ?>>ACTIVE</option>
        <option value="DISABLED"<? if ($form['status'] == "DISABLED") print " selected"; ?>>DISABLED</option>
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
    <input type="button" name="btn_back" class="button" value="Back" onclick="window.location.href='/_storage/repositories';" />
</div>
</form>