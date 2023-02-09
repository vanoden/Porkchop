<style>
	ul {
		list-style-type: none;
	}
	a {
	    cursor:pointer;
	}
</style>

<script>
// check or uncheck all boxes for ease of manage privileges
function checkUncheck(isChecked) {
    document.querySelectorAll('input[type=checkbox]').forEach(el => el.checked = isChecked);
}
</script>


<!-- Page Header -->
<?=$page->showBreadcrumbs()?>
<?=$page->showTitle()?>
<?=$page->showMessages()?>
<!-- End Page Header -->

<form method="post" action="/_register/role">
    <input type="hidden" name="name" value="<?=$role->name?>" />
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
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
        <div>
            <a onclick="checkUncheck(true)">&#10003; Check All</a> / <a onclick="checkUncheck(false)">&#10006; Uncheck All</a>
            <br/><br/>
        </div>
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
