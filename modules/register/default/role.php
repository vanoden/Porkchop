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

  // Handle checkbox changes - prevent Administrator from auto-checking lower levels
  function handlePrivilegeLevelChange(privilegeId, changedCheckbox) {
    var checkboxValue = parseInt(changedCheckbox.value);
    var privilegeRow = changedCheckbox.closest('.tableRow');
    
    // If Administrator (63) is being checked, don't auto-check lower levels
    if (checkboxValue === 63 && changedCheckbox.checked) {
      // Administrator is being checked - ensure lower levels stay as they are
      // Don't automatically check them
      return;
    }
    
    // If Administrator (63) is being unchecked, don't auto-uncheck lower levels
    if (checkboxValue === 63 && !changedCheckbox.checked) {
      // Administrator is being unchecked - lower levels stay independent
      return;
    }
    
    // For other levels, they're independent - no special handling needed
    // With addition-based arithmetic, each level is independent
  }
  
  // Prevent any automatic cascade behavior when page loads
  document.addEventListener('DOMContentLoaded', function() {
    // Ensure checkboxes are independent - no automatic checking/unchecking
    var allCheckboxes = document.querySelectorAll('input[name^="privilege_level"]');
    
    allCheckboxes.forEach(function(checkbox) {
      // Store the initial state to prevent unwanted changes
      checkbox.setAttribute('data-initial-state', checkbox.checked);
      
      // Add click handler to prevent cascade
      checkbox.addEventListener('click', function(e) {
        // Allow the checkbox to toggle normally
        // But don't let it affect other checkboxes
      }, true); // Use capture phase to run before other handlers
    });
    
    // Debug: Log form submission
    var form = document.querySelector('form[action="/_register/role"]');
    if (form) {
      form.addEventListener('submit', function(e) {
        console.log('Form submitting...');
        var privilegeCheckboxes = document.querySelectorAll('input[name^="privilege_level"]');
        privilegeCheckboxes.forEach(function(cb) {
          if (cb.checked) {
            console.log('Checked: ' + cb.name + ' = ' + cb.value);
          }
        });
      });
    }
  });
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

    <?php 
    // Check if user can modify role privileges
    $can_modify_privileges = true;
    if ($role->id && isset($GLOBALS['_SESSION_']->customer)) {
        $can_modify_privileges = $GLOBALS['_SESSION_']->customer->canModifyRolePrivileges($role);
    }
    ?>

    <?php if ($can_modify_privileges): ?>
      <button type="button" onclick="setAllNone()" class="button small">Set All None</button>
    <?php else: ?>
      <div class="note" style="margin-bottom: 10px;">You do not have permission to modify role privileges.</div>
    <?php endif; ?>
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
        // For form display, check if each level was explicitly included in the composition
        // With addition-based arithmetic, we check if we can subtract the level and get a valid remainder
        // Example: level 63 (administrator only) does NOT include 3 (sub-org) because 63 - 3 = 60 (invalid)
        // But level 66 (63 + 3) DOES include 3 because 66 - 3 = 63 (valid)
        $has_sub_org = \Register\PrivilegeLevel::levelIncludesExplicitly($current_level, 3);
        $has_org_manager = \Register\PrivilegeLevel::levelIncludesExplicitly($current_level, 7);
        $has_distributor = \Register\PrivilegeLevel::levelIncludesExplicitly($current_level, 15);
        $has_administrator = \Register\PrivilegeLevel::levelIncludesExplicitly($current_level, 63);
        
        // Debug: Only show for specific privilege if needed
        // if ($privilege->name == 'create email') {
        //     error_log("Privilege: {$privilege->name}, Level: $current_level, Sub-Org: " . ($has_sub_org ? 'yes' : 'no') . ", Org: " . ($has_org_manager ? 'yes' : 'no') . ", Dist: " . ($has_distributor ? 'yes' : 'no') . ", Admin: " . ($has_administrator ? 'yes' : 'no'));
        // }
        ?>
        <div class="tableRow">
          <div class="tableCell"><?= $privilege->module ?: 'No Module' ?></div>
          <div class="tableCell">
            <?= $privilege->description ?: $privilege->name ?: 'No Description' ?>
            <?php if ($current_level > 0): ?>
              <span style="font-size: 9px; color: #666;">(Level: <?= $current_level ?>)</span>
            <?php endif; ?>
          </div>
           <div class="tableCell role-column-cell">
             <label class="checkbox-label-container">
               <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][]" value="3" <?php if ($has_sub_org)
                 echo 'checked'; ?> <?php if (!$can_modify_privileges) echo 'disabled'; ?> onchange="handlePrivilegeLevelChange(<?= $privilege->id ?>, this)">
               <span class="checkbox-label-text">Sub-Org Manager</span>
             </label>
           </div>
          <div class="tableCell role-column-cell">
            <label class="checkbox-label-container">
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][]" value="7" <?php if ($has_org_manager)
                echo 'checked'; ?> <?php if (!$can_modify_privileges) echo 'disabled'; ?> onchange="handlePrivilegeLevelChange(<?= $privilege->id ?>, this)">
              <span class="checkbox-label-text">Org Manager</span>
            </label>
          </div>
          <div class="tableCell role-column-cell">
            <label class="checkbox-label-container">
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][]" value="15" <?php if ($has_distributor)
                echo 'checked'; ?> <?php if (!$can_modify_privileges) echo 'disabled'; ?> onchange="handlePrivilegeLevelChange(<?= $privilege->id ?>, this)">
              <span class="checkbox-label-text">Distributor</span>
            </label>
          </div>
          <div class="tableCell role-column-cell">
            <label class="checkbox-label-container">
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][]" value="63" <?php if ($has_administrator)
                echo 'checked'; ?> <?php if (!$can_modify_privileges) echo 'disabled'; ?> onchange="handlePrivilegeLevelChange(<?= $privilege->id ?>, this)">
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