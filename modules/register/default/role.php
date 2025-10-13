<script>
// Set all privilege levels to administrator for ease of management
function setAllAdministrator() {
	var selectElements = document.querySelectorAll('select[name^="privilege_level"]');
	selectElements.forEach(function(el) {
		el.value = '63'; // Administrator level
	});
}

// Set all privilege levels to none
function setAllNone() {
	var selectElements = document.querySelectorAll('select[name^="privilege_level"]');
	selectElements.forEach(function(el) {
		el.value = '0'; // None level
	});
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
    <input type="text" name="description" class="width-400px" value="<?=strip_tags($role->description)?>" />
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
        <div class="tableCell role-privileges-select">
          <button type="button" onclick="setAllAdministrator()" class="button small">Set All Admin</button>
          <button type="button" onclick="setAllNone()" class="button small">Set All None</button>
        </div>
        <div class="tableCell width-15per">Privilege Level</div>
        <div class="tableCell width-20per">Privilege Module</div>
        <div class="tableCell width-45per">Description</div>
      </div>

<?php 
// Get current privilege levels for this role
$current_privilege_levels = array();
if ($role->id) {
    $role_privileges = $role->privileges();
    foreach ($role_privileges as $role_privilege) {
        $current_privilege_levels[$role_privilege->id] = $role_privilege->level ?? 0;
    }
}

foreach ($privileges as $privilege) { 
    $current_level = $current_privilege_levels[$privilege->id] ?? 0;
?>
      <div class="tableRow">
        <div class="tableCell role-privileges-level">
          <select name="privilege_level[<?=$privilege->id?>]" class="privilege-level-select">
            <option value="0" <?php if ($current_level == 0) echo 'selected'; ?>>None</option>
            <option value="3" <?php if ($current_level == 3) echo 'selected'; ?>>Sub-Organization Manager</option>
            <option value="7" <?php if ($current_level == 7) echo 'selected'; ?>>Organization Manager</option>
            <option value="15" <?php if ($current_level == 15) echo 'selected'; ?>>Distributor</option>
            <option value="63" <?php if ($current_level == 63) echo 'selected'; ?>>Administrator</option>
          </select>
        </div>
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
