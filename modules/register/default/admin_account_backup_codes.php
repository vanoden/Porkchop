<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php $activeTab = 'backup_codes'; ?>

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
</div>

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account_backup_codes?customer_id=<?= $customer_id ?>" method="POST">

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
  <!-- BACKUP CODES -->
  <!-- ============================================== -->
  <h3>Backup Codes</h3>
  <div class="tableBody min-tablet">
    <p><strong>Generate 6 backup codes for this user. Generating new codes will erase all previous backup codes.</strong></p>
    <input type="submit" class="button" name="generate_backup_codes" value="Generate Backup Codes">
    <?php if (isset($generatedBackupCodes) && is_array($generatedBackupCodes)) { ?>
      <div class="backup-codes-list margin-top-10px">
        <p><strong>New Backup Codes:</strong></p>
        <table class="table-backup-codes">
          <tr><th>Code</th><th>Status</th></tr>
          <?php if (!empty($generatedBackupCodes)) { foreach ($generatedBackupCodes as $code) { ?>
            <tr>
              <td class="register-admin-account-backup-codes-td">
                <?= htmlentities($code) ?>
              </td>
              <td class="register-admin-account-backup-codes-status-td">
                <span class="register-admin-account-backup-codes-unused">Unused</span>
              </td>
            </tr>
          <?php } } ?>
        </table>
      </div>
    <?php } ?>
    <?php if (isset($allBackupCodes) && count($allBackupCodes) > 0 && !isset($generatedBackupCodes)) { ?>
      <div class="backup-codes-list margin-top-10px">
        <p><strong>Current Backup Codes:</strong></p>
        <table class="table-backup-codes">
          <tr><th>Code</th><th>Status</th></tr>
          <?php if (!empty($allBackupCodes)) { foreach ($allBackupCodes as $bcode) { ?>
            <tr>
              <td class="register-admin-account-backup-codes-td">
                <?= htmlentities($bcode['code']) ?>
              </td>
              <td class="register-admin-account-backup-codes-status-td">
                <?php if ($bcode['used']) { ?>
                  <span class="register-admin-account-backup-codes-used">Used</span>
                <?php } else { ?>
                  <span class="register-admin-account-backup-codes-unused">Unused</span>
                <?php } ?>
              </td>
            </tr>
          <?php } } ?>
        </table>
      </div>
    <?php } ?>
  </div>
  <!-- End Backup Codes Section -->

  <!-- entire page button submit -->
  <div id="submit-button-container" class="tableBody min-tablet">
    <div class="tableRow button-bar">
      <input id="btn_submit" type="submit" name="method" class="button" value="Apply" />
    </div>
  </div>
</form>
