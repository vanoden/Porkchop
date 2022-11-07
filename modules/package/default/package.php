<?php if ($package->id) { ?>
    <div class="title">Package <?=$package->code?></div>
<?php } else { ?>
    <div class="title">New Package</div>
<?php } ?>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<form name="packageForm" method="POST" action="/_package/package">
    <input type="hidden" name="package_id" value="<?=$package->id?>" />
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <?php if ($package->id) { ?>
        <div class="container">
            <span class="label">Created</span>
            <span class="value"><?=$package->date_created?></span>
        </div>
    <?php } else { ?>
        <div class="container">
            <span class="label">Code</span>
            <input type="text" name="code" class="value input wide_xl" value="" />
        </div>
    <?php } ?>
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
    <?php foreach ($admins as $owner) { ?>
            <option value="<?=$owner->id?>"<?php	if ($package->owner->id == $owner->id) print " selected"; ?>><?=$owner->code?></option>
    <?php } ?>
        </select>
    </div>
    <div class="container">
        <span class="label">Repository</span>
    <?php if ($package->id) { ?>
        <span class="value"><?=$package->repository->name?></span>
    <?php } else { ?>
        <select name="repository_id" class="value input wide_xl">
    <?php foreach ($repositories as $repository) { ?>
            <option value="<?=$repository->id?>"><?=$repository->name?></option>
    <?php } ?>
        </select>
    <?php } ?>
    <div class="container">
        <span class="label">Status</span>
        <select name="status" class="value input wide_xl">
            <option value="NEW"<?php	if ($package->status == "NEW") print " selected"; ?>>NEW</option>
            <option value="ACTIVE"<?php	if ($package->status == "ACTIVE") print " selected"; ?>>ACTIVE</option>
            <option value="HIDDEN"<?php	if ($package->status == "HIDDEN") print " selected"; ?>>HIDDEN</option>
        </select>
    </div>
    <div class="form_footer">
        <input type="submit" name="btn_submit" value="Update" class="button" />
        <input type="button" name="btn_ver" value="Versions" class="button" onclick="window.location.href='/_package/versions?code=<?=$package->code?>'" />
        <input type="button" name="btn_back" value="Back" class="button" onclick="window.location.href='/_package/packages'" />
    </div>
</form>
