<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php
$activeTab = 'auth_failures';
require __DIR__ . '/admin_account_tabs.php';
?>

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
      <li>Auth Failures Since Last Success: <?= $customer->auth_failures() ?></li>
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
    <input type="button" name="btnAuditLog" value="Audit Log" onclick="location.href='/_register/admin_account_audit_log?customer_id=<?= $customer->id ?>';" />
  </div>

  <!-- entire page button submit -->
  <div class="form-actions filter-bar">
    <div class="button-group filter-bar__actions">
      <button type="submit" id="btn_submit" name="method" class="button" value="Apply" onclick="return submitForm();">Apply</button>
    </div>
  </div>
</form>
	</div><!-- .register-admin-account__content -->
</div><!-- .register-admin-account -->
