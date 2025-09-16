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

<div class="form_instruction">
	<strong>Role Management:</strong> Configure role details and assign privileges to control user access to system functionality.
</div>

<form method="post" action="/_register/role">
  <input type="hidden" name="name" value="<?=isset($role) && $role ? $role->name : ''?>" />
  <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
  <input type="hidden" name="id" value="<?=isset($role) && $role ? $role->id : ''?>">

  <h3>Role Information</h3>
  <section class="tableBody clean min-tablet">
    <div class="tableRowHeader">
      <div class="tableCell width-25per">Field</div>
      <div class="tableCell width-75per">Value</div>
    </div>
    <div class="tableRow">
      <div class="tableCell">
        <span class="label">Role Name</span>
      </div>
      <div class="tableCell">
        <?php if (isset($role) && $role && $role->id) { ?>
          <span class="value"><?=htmlspecialchars($role->name)?></span>
        <?php } else { ?>
          <input type="text" name="name" class="value input width-100per" value="<?=isset($role) && $role ? htmlspecialchars($role->name) : ''?>" placeholder="Enter role name" required />
        <?php } ?>
      </div>
    </div>
    <div class="tableRow">
      <div class="tableCell">
        <span class="label">Description</span>
      </div>
      <div class="tableCell">
        <input type="text" name="description" class="value input width-100per" value="<?=isset($role) && $role ? htmlspecialchars(strip_tags($role->description)) : ''?>" placeholder="Enter role description" />
      </div>
    </div>
    <?php if ($GLOBALS['_config']->register->use_otp) { ?>
    <div class="tableRow">
      <div class="tableCell">
        <span class="label">Two-Factor Authentication</span>
      </div>
      <div class="tableCell">
        <div class="checkbox-row">
          <input type="checkbox" id="totpCB" name="time_based_password" value="1" <?php if (isset($role) && $role && !empty($role->time_based_password)) echo "checked"; ?>>
          <label for="totpCB" class="checkbox-label">Require Two-Factor Authentication</label>
        </div>
        <div class="help-text">If enabled, all users with this role will be required to use two-factor authentication</div>
      </div>
    </div>
    <?php } ?>
  </section>

  <h3>Role Privileges</h3>
  <div class="marginBottom_10">
    <a href="/_register/privileges" class="button secondary">Manage Privileges</a>
  </div>

  <?php if (isset($privileges) && count($privileges) > 0) { ?>
  <section class="tableBody clean min-tablet">
    <div class="tableRowHeader">
      <div class="tableCell width-10per">
        <div class="checkbox-row">
          <input type="checkbox" id="checkAll" name="checkAll" value="1" onclick="checkUncheck()" <?php if (isset($allChecked) && $allChecked) print "checked";?>>
          <label for="checkAll" class="checkbox-label">Select All</label>
        </div>
      </div>
      <div class="tableCell width-25per">Module</div>
      <div class="tableCell width-65per">Privilege Name</div>
    </div>

<?php foreach ($privileges as $privilege) { ?>
    <div class="tableRow">
      <div class="tableCell">
        <input type="checkbox" name="privilege[<?=$privilege->id?>]" value="1"<?php if (isset($role) && $role && $role->has_privilege($privilege->id)) print " checked";?>>
      </div>
      <div class="tableCell">
        <span class="value"><?=htmlspecialchars($privilege->module ?? '')?></span>
      </div>
      <div class="tableCell">
        <span class="value"><?=htmlspecialchars($privilege->name)?></span>
      </div>
    </div>
<?php } ?>

  </section>
  <?php } else { ?>
  <div class="marginTop_20">
    <div class="value" style="text-align: center; color: #666; padding: 20px;">
      No privileges available. <a href="/_register/privileges">Create privileges</a> to assign to this role.
    </div>
  </div>
  <?php } ?>

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
