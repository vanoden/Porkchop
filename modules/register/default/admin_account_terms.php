<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php $activeTab = 'terms'; ?>

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

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account_terms?customer_id=<?= $customer_id ?>" method="POST">

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
  <!-- TERMS OF USE HISTORY -->
  <!-- ============================================== -->
  <h3>Terms of Use History</h3>
  <div id="terms-of-use-table" class="register-admin-terms-table">
    <div class="tableBody min-tablet">
      <div class="tableRowHeader">
        <div class="tableCell">Code</div>
        <div class="tableCell">Name</div>
        <div class="tableCell">Description</div>
        <div class="tableCell">Last Action</div>
        <div class="tableCell">Last Action Date</div>
      </div>
      <?php if (!empty($terms)) { foreach ($terms as $term) { ?>
        <div class="tableRow">
          <div class="tableCell"><?= $term->code ?></div>
          <div class="tableCell"><?= $term->name ?></div>
          <div class="tableCell"><?= strip_tags($term->description) ?></div>
          <div class="tableCell">
            <?php
            $mostRecentAction = $termsOfUseActionList->find(array('user_id' => $customer->id, 'version_id' => $term->id, 'sort' => 'date_action', 'order' => 'DESC', 'limit' => 1));
            $mostRecentActionDate = "";
            if (!is_array($mostRecentAction)) {
              $mostRecentAction = array_pop($mostRecentAction);
              if (isset($mostRecentAction->type)) print $mostRecentAction->type;
              if (isset($mostRecentAction->type)) $mostRecentActionDate = date('m/d/Y', strtotime($mostRecentAction->date_action));
            }
            ?>
          </div>
          <div class="tableCell">
            <?= $mostRecentActionDate ?>
          </div>
        </div>
      <?php } } ?>
    </div>
  </div>

  <!-- entire page button submit -->
  <div id="submit-button-container" class="tableBody min-tablet">
    <div class="tableRow button-bar">
      <input id="btn_submit" type="submit" name="method" class="button" value="Apply" />
    </div>
  </div>
</form>
