<script>
  (function () {
    var PRIVILEGE_LEVELS = [1, 2, 3, 5, 7];

    function getColumnCheckboxes(level) {
      return document.querySelectorAll(
        'input[type="checkbox"][name^="privilege_level["][name$="[' + level + ']"]'
      );
    }

    function getHeaderCheckbox(level) {
      return document.querySelector('.role-privilege-column-toggle[data-level="' + level + '"]');
    }

    function isColumnFullyChecked(level) {
      var boxes = getColumnCheckboxes(level);
      if (!boxes.length) {
        return false;
      }
      return Array.from(boxes).every(function (el) {
        return el.checked;
      });
    }

    function syncHeaderCheckbox(level) {
      var header = getHeaderCheckbox(level);
      if (header) {
        header.checked = isColumnFullyChecked(level);
      }
    }

    function syncAllHeaderCheckboxes() {
      PRIVILEGE_LEVELS.forEach(syncHeaderCheckbox);
    }

    function setColumnChecked(level, checked) {
      getColumnCheckboxes(level).forEach(function (el) {
        el.checked = checked;
      });
      syncHeaderCheckbox(level);
    }

    window.setAllNone = function () {
      document.querySelectorAll('input[type="checkbox"][name^="privilege_level["]').forEach(function (el) {
        el.checked = false;
      });
      syncAllHeaderCheckboxes();
    };

    document.addEventListener('DOMContentLoaded', function () {
      PRIVILEGE_LEVELS.forEach(function (level) {
        var header = getHeaderCheckbox(level);
        if (header) {
          header.addEventListener('change', function () {
            setColumnChecked(level, header.checked);
          });
        }

        getColumnCheckboxes(level).forEach(function (checkbox) {
          checkbox.addEventListener('change', function () {
            syncHeaderCheckbox(level);
          });
        });
      });

      syncAllHeaderCheckboxes();
    });
  })();
</script>

<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<form method="post" action="/_register/role">
  <input type="hidden" name="name" value="<?= $role->name ?>" />
  <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
  <input type="hidden" name="id" value="<?= $role->id ?>">

  <section class="section-grid grid-col-4" aria-labelledby="role-details-heading">

    <div class="form-field">
      <label for="role_name">Role Name</label>
      <?php if ($role->id) { ?>
        <input type="text" id="role_name" name="name" value="<?= htmlspecialchars($role->name, ENT_QUOTES, 'UTF-8') ?>" required aria-required="true">
      <?php } else { ?>
        <input type="text" id="role_name" name="name" placeholder="e.g. Engineering User" value="" required aria-required="true">
      <?php } ?>
    </div>

    <div class="form-field">
      <label for="role-description">Description</label>
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

  <section id="rolePrivilegesContainer">

    <div class="section-flex cluster">
      <button href="/_register/privileges">Manage Privileges</button>
      <?php 
      // Check if user can modify role privileges
      $can_modify_privileges = true;
      if ($role->id && isset($GLOBALS['_SESSION_']->customer)) {
          $can_modify_privileges = $GLOBALS['_SESSION_']->customer->canModifyRolePrivileges($role);
      }
      ?>
      <?php if ($can_modify_privileges): ?>
        <button type="button" onclick="setAllNone()" class="btn-secondary">Set All None</button>
      <?php else: ?>
        <div class="note" style="margin-bottom: 10px;">You do not have permission to modify role privileges.</div>
      <?php endif; ?>
    </div>


    <table class="responsive-table responsive-table--banded">
      <colgroup>
        <col class="col-w-15">
        <col>
        <col class="col-w-10">
        <col class="col-w-10">
        <col class="col-w-10">
        <col class="col-w-10">
        <col class="col-w-10">
      </colgroup>
      <thead>
        <tr>
          <th scope="col">Module</th>
          <th scope="col">Description</th>
          <th scope="col">
            <label class="form-check-stack form-font--compact">
              <span>Customer</span>
              <input type="checkbox" class="role-privilege-column-toggle" data-level="<?= \Register\PrivilegeLevel::CUSTOMER ?>">
            </label>
          </th>
          <th scope="col">
            <label class="form-check-stack form-font--compact">
              <span>Sub-Org Mgr</span>
              <input type="checkbox" class="role-privilege-column-toggle" data-level="<?= \Register\PrivilegeLevel::SUB_ORGANIZATION_MANAGER ?>">
            </label>
          </th>
          <th scope="col">
            <label class="form-check-stack form-font--compact">
              <span>Org Mgr</span>
              <input type="checkbox" class="role-privilege-column-toggle" data-level="<?= \Register\PrivilegeLevel::ORGANIZATION_MANAGER ?>">
            </label>
          </th>
          <th scope="col">
            <label class="form-check-stack form-font--compact">
              <span>Distributor</span>
              <input type="checkbox" class="role-privilege-column-toggle" data-level="<?= \Register\PrivilegeLevel::DISTRIBUTOR ?>">
            </label>
          </th>
          <th scope="col">
            <label class="form-check-stack form-font--compact">
              <span>Administrator</span>
              <input type="checkbox" class="role-privilege-column-toggle" data-level="<?= \Register\PrivilegeLevel::ADMINISTRATOR ?>">
            </label>
          </th>
        </tr>
      </thead>
      <tbody>
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
        <tr>
          <td data-label="Module"><?= $privilege->module ?: 'No Module' ?></td>
          <td data-label="Description"><?= $privilege->description ?: $privilege->name ?: 'No Description' ?></td>

          <td data-label="Customer" class="text-align--center">
            <label class="checkbox-label-container">
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][<?= \Register\PrivilegeLevel::CUSTOMER ?>]" value="1" <?php if ($role->has_privilege($privilege->id, \Register\PrivilegeLevel::CUSTOMER)) echo 'checked'; ?>>
            </label>
          </td>

          <td data-label="Sub-Org Mgr" class="text-align--center">
            <label>
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][<?= \Register\PrivilegeLevel::SUB_ORGANIZATION_MANAGER ?>]" value="1" <?php if ($role->has_privilege($privilege->id, \Register\PrivilegeLevel::SUB_ORGANIZATION_MANAGER)) echo 'checked'; ?>>
            </label>
          </td>

          <td data-label="Org Mgr" class="text-align--center">
            <label class="checkbox-label-container">
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][<?= \Register\PrivilegeLevel::ORGANIZATION_MANAGER ?>]" value="1" <?php if ($role->has_privilege($privilege->id, \Register\PrivilegeLevel::ORGANIZATION_MANAGER)) echo 'checked'; ?>>
            </label>
          </td>

          <td data-label="Distributor" class="text-align--center">
            <label class="checkbox-label-container">
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][<?= \Register\PrivilegeLevel::DISTRIBUTOR ?>]" value="1" <?php if ($role->has_privilege($privilege->id, \Register\PrivilegeLevel::DISTRIBUTOR)) echo 'checked'; ?>>
            </label>
          </td>

          <td data-label="Administrator" class="text-align--center">
            <label class="checkbox-label-container">
              <input type="checkbox" name="privilege_level[<?= $privilege->id ?>][<?= \Register\PrivilegeLevel::ADMINISTRATOR ?>]" value="1" <?php if ($role->has_privilege($privilege->id, \Register\PrivilegeLevel::ADMINISTRATOR)) echo 'checked'; ?>>
            </label>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <!-- END TABLE -->

    </section>

  <!-- entire page button submit -->
  <div class="section-flex cluster">
    <?php if (isset($role->id)) { ?>
        <button type="submit" name="btn_submit" class="button" value="Update">Update</button>
      <?php } else { ?>
        <button type="submit" name="btn_submit" class="button" value="Create">Create</button>
      <?php } ?>
  </div>
</form>