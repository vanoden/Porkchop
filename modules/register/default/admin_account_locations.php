<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php $activeTab = 'locations'; ?>

<div class="tabs">
    <a href="/_register/admin_account?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='login'?'active':'' ?>">Login / Registration</a>
    <a href="/_register/admin_account_contacts?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='contacts'?'active':'' ?>">Methods of Contact</a>
    <a href="/_register/admin_account_password?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='password'?'active':'' ?>">Change Password</a>
    <a href="/_register/admin_account_roles?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='roles'?'active':'' ?>">Assigned Roles</a>
    <a href="/_register/admin_account_auth_failures?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='auth_failures'?'active':'' ?>">Recent Auth Failures</a>
    <a href="/_register/admin_account_terms?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='terms'?'active':'' ?>">Terms of Use History</a>
    <a href="/_register/admin_account_locations?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='locations'?'active':'' ?>">Locations</a>
    <a href="/_register/admin_account_images?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='images'?'active':'' ?>">User Images</a>
    <a href="/_register/admin_account_backup_codes?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='backup_codes'?'active':'' ?>">Backup Codes</a>
    <a href="/_register/admin_account_search_tags?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='search_tags'?'active':'' ?>">Search Tags</a>
    <a href="/_register/admin_account_audit_log?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
    <a href="/_register/admin_account_register_audit?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='register_audit'?'active':'' ?>">Register Audit</a>
</div>

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account_locations?customer_id=<?= $customer_id ?>" method="POST">

  <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
  <input type="hidden" name="target" value="<?= $target ?>" />
  <input type="hidden" name="customer_id" value="<?= $customer_id ?>" />
  <input type="hidden" name="login" value="<?= $customer->code ?>" />

  <section id="form-message">
    <ul class="connectBorder infoText">
      <li>Make changes and click 'Apply' to complete.</li>
    </ul>
  </section>

  <!-- ============================================== -->
  <!-- LOCATIONS -->
  <!-- ============================================== -->
  <h3>Locations</h3>
  <p>
    <input type="button" class="button" value="Add Location" onclick="window.location.href='/_register/admin_location?user_id=<?= (int)$customer->id ?>&amp;customer_id=<?= (int)$customer_id ?>';" />
    <form method="get" action="/_register/admin_account_locations" class="inline-form marginLeft_10" style="display:inline;">
      <input type="hidden" name="customer_id" value="<?= (int)$customer_id ?>" />
      <label><input type="checkbox" name="show_hidden" value="1" <?= !empty($show_hidden) ? 'checked' : '' ?> onchange="this.form.submit()" /> Show hidden addresses</label>
    </form>
  </p>
  <div class="table width-80per">
    <div class="tableRowHeader">
      <div class="tableCell width-20per">Name</div>
      <div class="tableCell width-50per">Address</div>
      <div class="tableCell width-30per">Actions</div>
    </div>
    <?php if (!empty($locations)) { foreach ($locations as $location) {
      $is_org_location = isset($org_location_ids) && in_array($location->id, $org_location_ids);
      $is_hidden = !empty($location->hidden);
    ?>
      <div class="tableRow"<?= $is_hidden ? ' style="color: #999;"' : '' ?>>
        <div class="tableCell width-20per"><?php
          if ($is_org_location && isset($organization) && $organization->id) {
            $locLink = '/_register/admin_organization_locations/' . htmlspecialchars($organization->code) . '?organization_id=' . (int)$organization->id;
            echo '<a href="' . $locLink . '">' . htmlspecialchars($location->name) . '</a> <span class="value">(' . htmlspecialchars($organization->name) . ')</span>';
          } else {
            echo htmlspecialchars($location->name);
          }
        ?></div>
        <div class="tableCell width-50per"><?= $location->HTMLBlockFormat() ?></div>
        <div class="tableCell width-30per">
          <a href="/_register/admin_location?user_id=<?= (int)$customer->id ?>&amp;customer_id=<?= (int)$customer_id ?>&amp;copy_id=<?= $location->id ?>">Copy</a>
          <?php if ($is_hidden) { ?>
          | <a href="/_register/admin_account_locations?customer_id=<?= (int)$customer_id ?>&amp;setVisible=<?= $location->id ?>">Unhide</a>
          <?php } else { ?>
          | <a href="/_register/admin_account_locations?customer_id=<?= (int)$customer_id ?>&amp;setHidden=<?= $location->id ?>">Hide</a>
          <?php } ?>
        </div>
      </div>
    <?php   } } ?>
  </div>

  <!-- entire page button submit -->
  <div id="submit-button-container" class="tableBody min-tablet">
    <div class="tableRow button-bar">
      <input id="btn_submit" type="submit" name="method" class="button" value="Apply" />
    </div>
  </div>
</form>
