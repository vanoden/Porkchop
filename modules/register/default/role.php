<script>
  // Toggle all checkboxes for a specific privilege level
  function setAllPrivilegeLevel(value) {
    var checkboxElements = document.querySelectorAll('input[name^="privilege_level"][name$="][' + value + ']');

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
  function setAllCustomer() { setAllPrivilegeLevel(0); }
  function setAllSubOrg() { setAllPrivilegeLevel(2); }
  function setAllOrgManager() { setAllPrivilegeLevel(3); }
  function setAllDistributor() { setAllPrivilegeLevel(5); }
  function setAllAdministrator() { setAllPrivilegeLevel(7); }

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
    
    // If Administrator (7) is being checked, don't auto-check lower levels
    if (checkboxValue === 7 && changedCheckbox.checked) {
      // Administrator is being checked - ensure lower levels stay as they are
      // Don't automatically check them
      return;
    }
    
    // If Administrator (7) is being unchecked, don't auto-uncheck lower levels
    if (checkboxValue === 7 && !changedCheckbox.checked) {
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
  <input type="hidden" name="id" value="<?= $role->id ?>">

  <section class="role-details-section" aria-labelledby="role-details-heading">
    <h3 id="role-details-heading">Role details</h3>

    <div class="role-name-container">
      <span class="label role-name-label">Role name</span>
      <?php if ($role->id) { ?>
        <span class="value role-name-value"><?= htmlspecialchars($role->name) ?></span>
      <?php } else { ?>
        <input class="role-name-input" type="text" name="name" value="" required aria-required="true" />
      <?php } ?>
    </div>

    <div class="role-description-container">
      <label class="label role-description-label" for="role-description">Description</label>
      <input id="role-description" type="text" name="description" class="width-400px" value="<?= htmlspecialchars(strip_tags($role->description ?? '')) ?>" placeholder="e.g. Default Super User" aria-describedby="role-description-hint" />
      <span id="role-description-hint" class="sr-only">Short description of what this role is for.</span>
    </div>

    <?php
    $configuration = new \Site\Configuration();
    if ($configuration->getValueBool("use_otp")) { ?>
    <div class="role-twofactor-container">
      <label for="totpCB" class="label">Require two-factor authentication</label>
      <input type="checkbox" id="totpCB" name="time_based_password" value="1" <?= !empty($role->time_based_password) ? 'checked' : '' ?> aria-describedby="totp-note" />
      <span id="totp-note" class="note">If enabled, all users with this role must use two-factor authentication when signing in.</span>
    </div>
    <?php } ?>
  </section>

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
        <div class="tableCell width-16per">Module</div>
        <div class="tableCell width-40per">Description</div>
        <div class="tableCell width-16per text-center role-column-cell">
          <span style="display: block;">Customer</span>
          <input type="checkbox" onclick="setAllCustomer()"/>
        </div>
        <div class="tableCell width-16per">
          <span style="display: block;">Sub-Org Mgr</span>
          <input type="checkbox" onclick="setAllSubOrg()"/>
        </div>
        <div class="tableCell width-16per">
          <span>Org Mgr</span>
          <input type="checkbox" onclick="setAllOrgManager()"/>
        </div>
        <div class="tableCell width-16per">
          <span>Distributor</span>
          <input type="checkbox" onclick="setAllDistributor()"/>
        </div>
        <div class="tableCell width-16per">
          <span>Administrator</span>
          <input type="checkbox" onclick="setAllAdministrator()"/>
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
        ?>
        <div class="tableRow">
          <div class="tableCell"><?= $privilege->module ?: 'No Module' ?></div>
          <div class="tableCell">
            <?= $privilege->description ?: $privilege->name ?: 'No Description' ?>
          </div>
           <div class="tableCell role-column-cell">
             <label class="checkbox-label-container">
               <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][0]" value="1" <?php if ($role->has_privilege($privilege->id, 0)) echo 'checked'; ?> onchange="handlePrivilegeLevelChange(<?= $privilege->id ?>, this)">
             </label>
           </div>
           <div class="tableCell role-column-cell">
             <label class="checkbox-label-container">
               <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][2]" value="1" <?php if ($role->has_privilege($privilege->id, 2)) echo 'checked'; ?> onchange="handlePrivilegeLevelChange(<?= $privilege->id ?>, this)">
             </label>
           </div>
          <div class="tableCell role-column-cell">
            <label class="checkbox-label-container">
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][3]" value="1" <?php if ($role->has_privilege($privilege->id, 3)) echo 'checked'; ?> onchange="handlePrivilegeLevelChange(<?= $privilege->id ?>, this)">
            </label>
          </div>
          <div class="tableCell role-column-cell">
            <label class="checkbox-label-container">
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][5]" value="1" <?php if ($role->has_privilege($privilege->id, 5)) echo 'checked'; ?> onchange="handlePrivilegeLevelChange(<?= $privilege->id ?>, this)">
            </label>
          </div>
          <div class="tableCell role-column-cell">
            <label class="checkbox-label-container">
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][7]" value="1" <?php if ($role->has_privilege($privilege->id, 7)) echo 'checked'; ?> onchange="handlePrivilegeLevelChange(<?= $privilege->id ?>, this)">
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