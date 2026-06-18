<script type="text/javascript">
  // submit form
  function submitForm() {
    if (document.register.password.value.length > 0 || document.register.password_2.value.length > 0) {
      if (document.register.password.value.length < 6) {
        alert("Your password is too short.");
        return false;
      }

      if (document.register.password.value != document.register.password_2.value) {
        alert("Your passwords don't match.");
        return false;
      }
    }
    return true;
  }

  // Prevent form submission on Enter key press
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('admin-account-form').addEventListener('keydown', function(event) {
      if (event.key === 'Enter') {
        // Allow Enter in textareas
        if (event.target.tagName.toLowerCase() === 'textarea') {
          return true;
        }
        // Prevent default form submission
        event.preventDefault();
        return false;
      }
    });
  });
</script>

<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php
$activeTab = 'password';
require __DIR__ . '/admin_account_tabs.php';
?>

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account_password?customer_id=<?= $customer_id ?>" method="POST">

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
  <!-- CHANGE PASSWORD -->
  <!-- ============================================== -->
  <?php if ($customer->auth_method == 'local') { ?>
    <h3 class="marginTop_20">Change Password</h3>
    <section id="form-message">
      <ul class="connectBorder infoText">
        <li>Leave both fields empty for your password to stay the same.</li>
      </ul>
    </section>
    <div class="tableBody clean min-tablet">
      <div class="tableRowHeader">
        <div class="tableCell width-30per">New Password</div>
        <div class="tableCell width-30per">Confirm New Password</div>
      </div>
      <div class="tableRow">
        <div class="tableCell"><input type="password" class="value width-100per" name="password" /></div>
        <div class="tableCell"><input type="password" class="value width-100per" name="password_2" /></div>
      </div>
    </div>
  <?php } else { ?>
    <p>Password changes are not available for this authentication method.</p>
  <?php } ?>

  <!-- entire page button submit -->
  <div class="form-actions filter-bar">
    <div class="button-group filter-bar__actions">
      <button type="submit" id="btn_submit" name="method" class="button" value="Apply" onclick="return submitForm();">Apply</button>
    </div>
  </div>
</form>
	</div><!-- .register-admin-account__content -->
</div><!-- .register-admin-account -->
