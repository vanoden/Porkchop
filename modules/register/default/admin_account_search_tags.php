<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php
$activeTab = 'search_tags';
require __DIR__ . '/admin_account_tabs.php';
?>

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account_search_tags?customer_id=<?= $customer_id ?>" method="POST">

  <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
  <input type="hidden" name="target" value="<?= $target ?>" />
  <input type="hidden" name="customer_id" value="<?= $customer_id ?>" />
  <input type="hidden" name="login" value="<?= $customer->code ?>" />
  <input type="hidden" id="removeSearchTagId" name="removeSearchTagId" value="" />

  <section id="form-message">
    <ul class="connectBorder infoText">
      <li>Make changes and click 'Apply' to complete.</li>
    </ul>
  </section>

<?php
  $searchTagsTitle = 'Customer Search Tags';
  $searchTagRows = $registerCustomerSearchTags ?? [];
  $searchTagsFormId = 'admin-account-form';
  $searchTagsCategoryPlaceholder = 'Location';
  $searchTagsValuePlaceholder = 'New York';
  $searchTagsSubmitInForm = true;
  require BASE . '/modules/site/default/search_tags_editor.php';
?>

  <div class="form-actions filter-bar">
    <div class="button-group filter-bar__actions">
      <button type="submit" id="btn_submit" name="method" class="button" value="Apply" onclick="return submitForm();">Apply</button>
    </div>
  </div>
</form>
	</div><!-- .register-admin-account__content -->
</div><!-- .register-admin-account -->
