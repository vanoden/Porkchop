<?  if ($package->id) { ?>
<div class="title">Package <?=$package->code?></div>
<?  } else { ?>
<div class="title">New Package</div>
<?  } ?>
<?  if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?  } ?>
<form name="packageForm" method="POST" action="/_package/package">
<input type="hidden" name="package_id" value="<?=$package->id?>" />
<?  if ($package->id) { ?>
<div class="container">
    <span class="label">Created</span>
    <span class="value"><?=$package->date_created?></span>
</div>
<?  } else { ?>
<div class="container">
    <span class="label">Code</span>
    <input type="text" name="code" class="value input wide_xl" value="" />
</div>
<?  } ?>
<div class="container">
    <span class="label">Name</span>
    <input type="text" name="name" class="value input wide_xl" value="<?=$package->name?>" />
</div>
<div class="container">
    <span class="label">Description</span>
    <textarea name="description" class="value input wide_xl"><?=$package->description?></textarea>
</div>
<div class="container">
    <span class="label">Platform</span>
    <input type="text" name="platform" class="value input wide_xl" value="<?=$package->platform?>" />
</div>
<div class="container">
    <span class="label">License</span>
    <input type="text" name="license" class="value input wide_xl" value="<?=$package->license?>" />
</div>
<div class="container">
    <span class="label">Owner</span>
    <select name="owner_id" class="value input wide_xl">
<?  foreach ($admins as $owner) { ?>
        <option value="<?=$owner->id?>"<? if ($package->owner->id == $owner->id) print " selected"; ?>><?=$owner->code?></option>
<?  } ?>
    </select>
</div>
<div class="container">
    <span class="label">Repository</span>
<?  if ($package->id) { ?>
    <span class="value"><?=$package->repository->name?></span>
<?  }
    else {
?>
    <select name="repository_id" class="value input wide_xl">
<?      foreach ($repositories as $repository) { ?>
        <option value="<?=$repository->id?>"><?=$repository->name?></option>
<?  } ?>
    </select>
<?  } ?>
<div class="container">
    <span class="label">Status</span>
    <select name="status" class="value input wide_xl">
        <option value="NEW"<? if ($package->status == "NEW") print " selected"; ?>>NEW</option>
        <option value="ACTIVE"<? if ($package->status == "ACTIVE") print " selected"; ?>>ACTIVE</option>
        <option value="HIDDEN"<? if ($package->status == "HIDDEN") print " selected"; ?>>HIDDEN</option>
    </select>
</div>
<div class="form_footer">
    <input type="submit" name="btn_submit" value="Update" class="button" />
    <input type="button" name="btn_ver" value="Versions" class="button" onclick="window.location.href='/_package/versions?code=<?=$package->code?>'" />
    <input type="button" name="btn_back" value="Back" class="button" onclick="window.location.href='/_package/packages'" />
</div>
</form>