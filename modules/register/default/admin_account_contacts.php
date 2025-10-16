<script type="text/javascript">
  function removeSearchTagById(id) {
    document.getElementById('removeSearchTagId').value = id;
    submitForm();
  }

  // submit form
  function submitForm() {
    // make sure that all the notify contacts have a 'description' value populated
    var contactTable = document.getElementById("contact-main-table");
    if (!contactTable) return true; // If no contact table exists, continue with form submission
    
    var notifyChecked = contactTable.getElementsByTagName("input");
    for (var i = 0; i < notifyChecked.length; i++) {
      if (notifyChecked[i].checked) {
        var matches = notifyChecked[i].name.match(/\[[0-9]+\]/);
        if (matches && matches[0]) { // Add null check here
          var contactDescriptionField = document.getElementsByName("description[" + matches[0].replace('[', '').replace(']', '') + "]");
          if (contactDescriptionField && contactDescriptionField[0]) { // Add null check for contactDescriptionField
            contactDescriptionField[0].style.border = "";
            if (!contactDescriptionField[0].value) {
              alert("Please enter a 'Description' value for all notify (checked) Methods of Contact");
              contactDescriptionField[0].style.border = "3px solid red";
              return false;
            }
          }
        }
      }
    }
    return true;
  }

  // submit a delete contact with the hidden form
  function submitDelete(contactId) {
    var confirmDelete = confirm("Delete contact entry for user?");
    if (confirmDelete == true) {
      document.getElementById("register-contacts-id").value = contactId;
      document.getElementById("delete-contact").submit();
      return false;
    }
  }

  function enableNewContact() {
    document.getElementById('new-description').style.display = "block";
    document.getElementById('new-value').style.display = "block";
    document.getElementById('new-notes').style.display = "block";
    document.getElementById('new-notify').style.display = "block";
    document.getElementById('new-public').style.display = "block";
    var newContactSelect = document.getElementById("new-contact-select");
    newContactSelect.remove(5);
  }

  // remove an organization tag by id
  function removeSearchTagById(id) {
    document.getElementById('removeSearchTagId').value = id;
    document.getElementById('admin-account-form').submit();
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

<?php
$page->template = 'admin.html';
?>

<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php $activeTab = 'contacts'; ?>

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

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account_contacts?customer_id=<?= $customer_id ?>" method="POST">

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
  <!-- METHODS OF CONTACT -->
  <!-- ============================================== -->
  <h3>Methods of Contact</h3>
  <div id="contact-main-table" class="tableBody min-tablet">
    <div class="tableRowHeader">
      <div class="tableCell width-20per">Type</div>
      <div class="tableCell width-25per">Description</div>
      <div class="tableCell width-25per">Address/Number or Email etc.</div>
      <div class="tableCell width-10per">Notes</div>
      <div class="tableCell width-5per">Notify</div>
      <div class="tableCell width-5per">Public</div>
      <div class="tableCell width-5per">Drop</div>
    </div>
    <?php if (!empty($contacts)) { foreach ($contacts as $contact) { ?>
      <div class="tableRow">
        <div class="tableCell">
          <label class="display-none value">Type: </label>
          <select class="value input" name="type[<?= $contact->id ?>]">
            <?php foreach (array_keys($contact_types) as $contact_type) { ?>
              <option value="<?= $contact_type ?>" <?php if ($contact_type == $contact->type) print " selected"; ?>><?= $contact_types[$contact_type] ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="tableCell"><input type="text" name="description[<?= $contact->id ?>]" class="value width-100per" value="<?= htmlentities($contact->description) ?>" /></div>
        <div class="tableCell"><input type="text" name="value[<?= $contact->id ?>]" class="value width-100per" value="<?= htmlentities($contact->value) ?>" /></div>
        <div class="tableCell"><input type="text" name="notes[<?= $contact->id ?>]" class="value width-100per" value="<?= htmlentities($contact->notes) ?>" /></div>
        <div class="tableCell"><input type="checkbox" name="notify[<?= $contact->id ?>]" value="1" <?php if ($contact->notify) print "checked"; ?> /></div>
        <div class="tableCell"><input type="checkbox" name="public[<?= $contact->id ?>]" value="1" <?php if ($contact->public) print "checked"; ?> /></div>
        <div class="tableCell"><img class="width-30px register-admin-account-contact-drop-icon" name="drop_contact[<?= $contact->id ?>]" src="/img/icons/icon_tools_trash_active.svg" onclick="submitDelete(<?= $contact->id ?>)" /></div>
      </div>
      <!-- New contact entry -->
    <?php } } ?>
    <div class="tableRow">
      <div class="tableCell">
        <strong>Add New Contact:</strong>
        <select id="new-contact-select" class="value input" name="type[0]" onchange="enableNewContact()">
          <?php foreach (array_keys($contact_types) as $contact_type) { ?>
            <option value="<?= $contact_type ?>"><?= $contact_types[$contact_type] ?></option>
          <?php } ?>
          <option value="0" selected="selected">Select</option>
        </select>
      </div>
      <div class="tableCell"><br /><input type="text" id="new-description" name="description[0]" class="value width-100per register-admin-account-new-contact-hidden" /></div>
      <div class="tableCell"><br /><input type="text" id="new-value" name="value[0]" class="value width-100per register-admin-account-new-contact-hidden" /></div>
      <div class="tableCell"><br /><input type="text" id="new-notes" name="notes[0]" class="value width-100per register-admin-account-new-contact-hidden" /></div>
      <div class="tableCell"><br /><input type="checkbox" id="new-notify" name="notify[0]" value="1" class="register-admin-account-new-contact-hidden" /></div>
      <div class="tableCell"><br /><input type="checkbox" id="new-public" name="public[0]" value="1" class="register-admin-account-new-contact-hidden" /></div>
      <div class="tableCell"></div>
    </div>
  </div>
  <!--	END Methods of Contact -->

  <?php if ($customer->profile == "public") { ?>
  <!-- Business Card Preview Link -->
  <div class="register-admin-account-business-card-preview">
    <a href="/_register/businesscard?customer_id=<?= $customer_id ?>" class="button" target="_blank">Preview Business Card</a>
  </div>
  <?php } ?>

  <!-- entire page button submit -->
  <div id="submit-button-container" class="tableBody min-tablet">
    <div class="tableRow button-bar">
      <input id="btn_submit" type="submit" name="method" class="button" value="Apply" onclick="return submitForm();" />
    </div>
  </div>
</form>

<!-- hidden for for "delete contact" -->
<form id="delete-contact" name="delete-contact" action="<?= PATH ?>/_register/admin_account_contacts?customer_id=<?= $customer_id ?>" method="post">
  <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
  <input type="hidden" id="submit-type" name="submit-type" value="delete-contact" />
  <input type="hidden" id="register-contacts-id" name="register-contacts-id" value="" />
</form>
