<style>
  .role-column-cell {
    text-align: center;
    vertical-align: middle;
  }

  .bulk-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 10px;
  }

  .bulk-buttons button {
    font-size: 7px;
    padding: 4px 8px;
  }

  /* Checkbox label styles */
  .checkbox-label-container {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
  }

  .checkbox-label-text {
    margin-left: 4px;
    font-size: 8px;
  }
  .small {
    font-size: 8px;
    min-width: 100px;
  }

  .unset-all {
    background-color: #ff8c00;
    color: white;
  }
</style>


<script>
  // Toggle all checkboxes for a specific privilege level
  function setAllPrivilegeLevel(value) {
    var checkboxElements = document.querySelectorAll('input[name^="privilege_level"][value="' + value + '"]');
    var button = event.target;

    // Check if all are checked
    var allChecked = Array.from(checkboxElements).every(function (el) {
      return el.checked;
    });

    // If all are checked, uncheck all. Otherwise, check all.
    checkboxElements.forEach(function (el) {
      el.checked = !allChecked;
    });

    // Update button text and styling based on the NEW state
    if (allChecked) {
      // Was all checked, now all unchecked - show "Set All"
      button.textContent = 'Set All';
      button.classList.remove('unset-all');
    } else {
      // Was not all checked, now all checked - show "Unset All"
      button.textContent = 'Unset All';
      button.classList.add('unset-all');
    }
  }

  // Convenience functions for each privilege level
  function setAllSubOrg() { setAllPrivilegeLevel(3); }
  function setAllOrgManager() { setAllPrivilegeLevel(7); }
  function setAllDistributor() { setAllPrivilegeLevel(15); }
  function setAllAdministrator() { setAllPrivilegeLevel(63); }

  // Set all privilege levels to none
  function setAllNone() {
    var checkboxElements = document.querySelectorAll('input[name^="privilege_level"]');
    checkboxElements.forEach(function (el) {
      el.checked = false;
    });

    // Reset all toggle buttons to "Set All"
    var toggleButtons = document.querySelectorAll('button[onclick^="setAll"]:not([onclick="setAllNone()"])');
    toggleButtons.forEach(function (button) {
      button.textContent = 'Set All';
      button.classList.remove('unset-all');
    });
  }

  // Handle checkbox changes - allow multiple selections
  function handlePrivilegeLevelChange(privilegeId, changedCheckbox) {
    // No restrictions - allow multiple privilege levels to be selected
    // The backend will handle determining the appropriate permission level
  }
</script>


<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<form method="post" action="/_register/role">
  <input type="hidden" name="name" value="<?= $role->name ?>" />
  <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
  <div class="role-name-container">
    <span class="label role-name-label">Role Name</span>
    <?php if ($role->id) { ?>
      <span class="value role-name-value"><?= $role->name ?></span>
    <?php } else { ?>
      <input class="role-name-input" type="text" name="name" value="" />
    <?php } ?>
  </div>

  <div class="role-description-container">
    <span class="label role-description-label">Description</span>
    <input type="text" name="description" class="width-400px" value="<?= strip_tags($role->description) ?>" />
    <input type="hidden" name="id" value="<?= $role->id ?>">
  </div>

  <?php if ($GLOBALS['_config']->register->use_otp) { ?>
    <div>
      <label>Require Two-Factor Authentication</label>
      <input type="checkbox" id="totpCB" name="time_based_password" value="1" <?php if (!empty($role->time_based_password))
        echo "checked"; ?>>
      <span class="note">If enabled, all users with this role will be required to use two-factor authentication</span>
    </div>
  <?php } ?>

  <div id="rolePrivilegesContainer">

    <div id="search_container">
      <a href="/_register/privileges" class="register-role-manage-privileges-link">Manage Privileges</a>
    </div>

    <button type="button" onclick="setAllNone()" class="button small">Set All None</button>
    <div class="tableBody">

      <div class="tableRowHeader">
        <div class="tableCell width-20per">Privilege Module</div>
        <div class="tableCell width-40per">Description</div>
        <div class="tableCell">
          <button type="button" onclick="setAllSubOrg()" class="button small">Set All</button>
        </div>
        <div class="tableCell">
          <button type="button" onclick="setAllOrgManager()" class="button small">Set All</button>
        </div>
        <div class="tableCell">
          <button type="button" onclick="setAllDistributor()" class="button small">Set All</button>
        </div>
        <div class="tableCell">
          <button type="button" onclick="setAllAdministrator()" class="button small">Set All</button>
        </div>
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
          <div class="tableCell"><?= $privilege->module ?: 'No Module' ?></div>
          <div class="tableCell"><?= $privilege->description ?: $privilege->name ?: 'No Description' ?></div>
           <div class="tableCell role-column-cell">
             <label class="checkbox-label-container">
               <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][]" value="3" <?php if (($current_level & 3) == 3)
                 echo 'checked'; ?> onchange="handlePrivilegeLevelChange(<?= $privilege->id ?>, this)">
               <span class="checkbox-label-text">Sub-Org Manager</span>
             </label>
           </div>
          <div class="tableCell role-column-cell">
            <label class="checkbox-label-container">
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][]" value="7" <?php if (($current_level & 7) == 7)
                echo 'checked'; ?> onchange="handlePrivilegeLevelChange(<?= $privilege->id ?>, this)">
              <span class="checkbox-label-text">Org Manager</span>
            </label>
          </div>
          <div class="tableCell role-column-cell">
            <label class="checkbox-label-container">
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][]" value="15" <?php if (($current_level & 15) == 15)
                echo 'checked'; ?> onchange="handlePrivilegeLevelChange(<?= $privilege->id ?>, this)">
              <span class="checkbox-label-text">Distributor</span>
            </label>
          </div>
          <div class="tableCell role-column-cell">
            <label class="checkbox-label-container">
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][]" value="63" <?php if (($current_level & 63) == 63)
                echo 'checked'; ?> onchange="handlePrivilegeLevelChange(<?= $privilege->id ?>, this)">
              <span class="checkbox-label-text">Administrator</span>
            </label>
          </div>
        </div>
      <?php } ?>

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