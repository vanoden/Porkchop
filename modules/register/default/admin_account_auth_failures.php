<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php $activeTab = 'auth_failures'; ?>

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
</div>

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account_auth_failures?customer_id=<?= $customer_id ?>" method="POST">

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
  <!-- AUTH FAILURES -->
  <!-- ============================================== -->
  <h3>Recent Auth Failures</h3>

  <section id="form-message">
    <ul class="connectBorder infoText">
      <li>Auth Failures Since Last Success: <?= $customer->auth_failures ?></li>
    </ul>
  </section>

  <div id="auth-failures-table">
    <div class="tableBody min-tablet">
      <div class="tableRowHeader">
        <div class="tableCell">Date</div>
        <div class="tableCell">IP Address</div>
        <div class="tableCell">Reason</div>
        <div class="tableCell">Endpoint</div>
      </div>
      <?php if (!empty($authFailures)) { foreach ($authFailures as $authFailure) { ?>
        <div class="tableRow">
          <div class="tableCell"><?= $authFailure->date ?></div>
          <div class="tableCell"><?= $authFailure->ip_address ?></div>
          <div class="tableCell"><?= $authFailure->reason ?></div>
          <div class="tableCell"><?= $authFailure->endpoint ?></div>
        </div>
      <?php } } ?>
    </div>
    <input type="submit" name="btnResetFailures" value="Reset Failures" />
    <input type="button" name="btnAuditLog" value="Audit Log" onclick="location.href='/_register/audit_log?user_id=<?= $customer->id ?>';" />
  </div>

  <!-- entire page button submit -->
  <div id="submit-button-container" class="tableBody min-tablet">
    <div class="tableRow button-bar">
      <input id="btn_submit" type="submit" name="method" class="button" value="Apply" />
    </div>
  </div>
</form>
