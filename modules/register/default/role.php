<style>
	ul {
		list-style-type: none;
	}
</style>
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

<?=$page->showBreadcrumbs()?>
<form method="post" action="/_register/role">
    <input type="hidden" name="name" value="<?=$role->name?>" />
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <div class="title">Role Editor</div>
    <span class="label">Role Name</span>
    <?php if ($role->id) { ?>
	    <span class="value"><?=$role->name?></span>
    <?php } else { ?>
	    <input type="text" name="name" class="value input" value="" />
    <?php } ?>
    <span class="label">Description</span><input type="text" name="description" class="value input" value="<?=$role->description?>" />
    <input type="hidden" name="id" value="<?=$role->id?>">
    <div id="rolePrivilegesContainer">
        <span style="display: inline-block" class="label">Privileges</span>
        <a href="/_register/privileges">Manage</a>
        <?php	foreach ($privileges as $privilege) { ?>
	        <div class="rolePrivilegeContainer">
		        <span class="value" style="display: inline-block; width: 75px;"><?=$privilege->module?></span>
		        <span class="value" style="display: inline-block; width: 200px;"><?=$privilege->name?></span>
                <input type="checkbox" name="privilege[<?=$privilege->id?>]" value="1"<?php if ($role->has_privilege($privilege->id)) print " checked";?>>
	        </div>
        <?php	} ?>
    </div>

    <?php if (isset($role->id)) { ?>
        <input type="submit" name="btn_submit" class="button" value="Update">
    <?php } else { ?>
        <input type="submit" name="btn_submit" class="button" value="Create">
    <?php } ?>
</form>
