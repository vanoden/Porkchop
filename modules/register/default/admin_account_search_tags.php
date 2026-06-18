<!-- Autocomplete CSS and JS -->
<link href="/css/autocomplete.css" type="text/css" rel="stylesheet">
<script language="JavaScript" src="/js/autocomplete.js"></script>
<script language="JavaScript">
  // define existing categories and tags for autocomplete
  var existingCategories = <?= $uniqueTagsData['categoriesJson'] ?>;
  var existingTags = <?= $uniqueTagsData['tagsJson'] ?>;
</script>

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

  <!-- ============================================== -->
  <!-- CUSTOMER SEARCH TAGS -->
  <!-- ============================================== -->
  <h3 class="register-admin-account-search-tags-inline">Customer Search Tags</h3>
  <h4 class="register-admin-account-search-tags-inline">(customer support knowledge center)</h4>
  <div class="tableBody min-tablet">
    <div class="tableRowHeader">
      <div class="tableCell width-33per">Category</div>
      <div class="tableCell width-33per">Search Tag</div>
      <div class="tableCell width-33per">&nbsp;</div>
    </div>
    <?php
    if (!empty($registerCustomerSearchTags)) { foreach ($registerCustomerSearchTags as $row) {
      $searchTag = $row->searchTag;
      $xrefId = $row->xrefId;
    ?>
      <div class="tableRow">
        <div class="tableCell">
          <?= htmlspecialchars($searchTag->category) ?>
        </div>
        <div class="tableCell">
          <?= htmlspecialchars($searchTag->value) ?>
        </div>
        <div class="tableCell">
          <img src="/img/icons/icon_tools_trash_active.svg" onclick="removeSearchTagById('<?= (int)$xrefId ?>')" style="cursor: pointer; width: 20px; height: 20px;" alt="Remove" title="Remove" />
        </div>

      </div>

    <?php
    } }
    ?>
    <br />
    <div class="tableRow">
      <div class="tableCell">
        <label>Category:</label>
        <input type="text" class="autocomplete" name="newSearchTagCategory" id="newSearchTagCategory" value="" placeholder="Location" />
        <ul id="categoryAutocomplete" class="autocomplete-list"></ul>
      </div>
      <div class="tableCell">
        <label>New Search Tag:</label>
        <input type="text" class="autocomplete" name="newSearchTag" id="newSearchTag" value="" placeholder="New York" />
        <ul id="tagAutocomplete" class="autocomplete-list"></ul>
      </div>
    </div>
    <div><input type="submit" name="addSearchTag" value="Add Search Tag" class="button" /></div>
  </div>

  <!-- entire page button submit -->
  <div class="form-actions filter-bar">
    <div class="button-group filter-bar__actions">
      <button type="submit" id="btn_submit" name="method" class="button" value="Apply" onclick="return submitForm();">Apply</button>
    </div>
  </div>
</form>

<script type="text/javascript">
  function removeSearchTagById(id) {
    document.getElementById('removeSearchTagId').value = id;
    document.getElementById('admin-account-form').submit();
  }
</script>
	</div><!-- .register-admin-account__content -->
</div><!-- .register-admin-account -->
