<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php
$activeTab = 'terms';
require __DIR__ . '/admin_account_tabs.php';
?>

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
  <div class="form-actions filter-bar">
    <div class="button-group filter-bar__actions">
      <button type="submit" id="btn_submit" name="method" class="button" value="Apply" onclick="return submitForm();">Apply</button>
    </div>
  </div>
</form>
	</div><!-- .register-admin-account__content -->
</div><!-- .register-admin-account -->
