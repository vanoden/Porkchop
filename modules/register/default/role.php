<script>
// check or uncheck all boxes for ease of manage privileges
function checkUncheck() {
	var inputElem = document.getElementById("checkAll");
	if (inputElem.checked) {
		document.querySelectorAll('input[type=checkbox]').forEach(function(el) {
			if (el.id != "totpCB") el.checked = true;
		});
	}
	else {
    	document.querySelectorAll('input[type=checkbox]').forEach(function(el) {
			if (el.id != "totpCB") el.checked = false;
		});
	}
}
</script>


<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<form method="post" action="/_register/role">
  <input type="hidden" name="name" value="<?=$role->name?>" />
  <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
  <div class="role-name-container">
    <span class="label role-name-label">Role Name</span>
    <?php if ($role->id) { ?>
      <span class="value role-name-value"><?=$role->name?></span>
    <?php } else { ?>
      <input class="role-name-input" type="text" name="name" value="" />
    <?php } ?>
  </div>

  <div class="role-description-container">
    <span class="label role-description-label">Description</span>
    <input type="text" name="description" class="role-description-input" value="<?=strip_tags($role->description)?>" />
    <input type="hidden" name="id" value="<?=$role->id?>">
  </div>

  <?php if ($GLOBALS['_config']->register->use_otp) { ?>
  <div>
    <label>Require Two-Factor Authentication</label>
    <input type="checkbox" id="totpCB" name="time_based_password" value="1" <?php if (!empty($role->time_based_password)) echo "checked"; ?>>
    <span class="note">If enabled, all users with this role will be required to use two-factor authentication</span>
  </div>
  <?php } ?>

  <div id="rolePrivilegesContainer">

    <div id="search_container">
      <a href="/_register/privileges" class="register-role-manage-privileges-link">Manage Privileges</a>
    </div>


	  <div class="tableBody">

      <div class="tableRowHeader">
        <div class="tableCell role-privileges-select">Select <input type="checkbox" id="checkAll" name"checkAll" value="1" onclick="checkUncheck()" <?php if ($allChecked) print "checked";?>/></div>
        <div class="tableCell role-privileges-module">Privilege Module</div>
        <div class="tableCell role-privileges-description">Description</div>
      </div>

<?php foreach ($privileges as $privilege) { ?>
      <div class="tableRow">
        <div class="tableCell role-privileges-checkbox"><input type="checkbox" name="privilege[<?=$privilege->id?>]" value="1"<?php if ($role->has_privilege($privilege->id)) print " checked";?>></div>
        <div class="tableCell"><?=$privilege->module?></div>
        <div class="tableCell"><?=$privilege->name?></div>
      </div>
<?php	} ?>

    </div>

  </div>

  <!-- entire page button submit -->
  <div id="submit-button-container" class="tableBody min-tablet">
    <div class="tableRow button-bar">
      <?php if (isset($role->id)) { ?>
        <input type="submit" name="btn_submit" class="button" value="Update">
      <?php } else { ?>
        <input type="submit" name="btn_submit" class="button" value="Create">
      <?php } ?>
    </div>
  </div>
</form>
