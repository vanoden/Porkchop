<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php
$activeTab = 'roles';
require __DIR__ . '/admin_account_tabs.php';
?>

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account_roles?customer_id=<?= $customer_id ?>" method="POST">

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
  <!-- ASSIGNED ROLES -->
  <!-- ============================================== -->
  <h3>Assigned Roles</h3>
  <div class="tableBody min-tablet">
    <div class="tableRowHeader">
      <div class="tableCell width-20per">Select</div>
      <div class="tableCell width-25per">Name</div>
      <div class="tableCell width-30per">Description</div>
    </div>
    <?php foreach ($all_roles as $role) { ?>
      <div class="tableRow">
        <div class="tableCell"><input type="checkbox" name="role[<?= $role->id ?>]" value="1" <?php if ($customer->has_role($role->name)) print " CHECKED"; ?> /></div>
        <div class="tableCell"><?= $role->name ?></div>
        <div class="tableCell"><?= strip_tags($role->description) ?></div>
      </div>
    <?php } ?>
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
