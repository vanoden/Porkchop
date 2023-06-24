<script type="text/javascript">

  // submit form
  function submitForm() {

    // make sure that all the notify contacts have a 'description' value populated
    var contactTable = document.getElementById("contact-main-table");
    var notifyChecked = contactTable.getElementsByTagName("input");
    for (var i = 0; i < notifyChecked.length; i++) {
      if (notifyChecked[i].checked) {
        var matches = notifyChecked[i].name.match(/\[[0-9]+\]/);
        if (matches[0]) {
          contactDescriptionField = document.getElementsByName("description[" + matches[0].replace('[', '').replace(']', '') + "]");
          contactDescriptionField[0].style.border = "";
          if (!contactDescriptionField[0].value) {
            alert("Please enter a 'Description' value for all notify (checked) Methods of Contact");
            contactDescriptionField[0].style.border = "3px solid red";
            return false;
          }
        }
      }
    }

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

   // submit a delete contact with the hidden form
   function submitDelete(contactId) {
      var confirmDelete = confirm("Delete contact entry for user?");
      if (confirmDelete == true) {
         document.getElementById("register-contacts-id").value = contactId;
         document.getElementById("delete-contact").submit();
      }
   }

   function disableNewContact() {
    document.getElementById('disable-new-contact-button').style.display = "none";
    document.getElementById('new-description').style.display = "none";
    document.getElementById('new-value').style.display = "none";
    document.getElementById('new-notes').style.display = "none";
    document.getElementById('new-notify').style.display = "none";
    var newContactSelect = document.getElementById("new-contact-select");
    newContactSelect.options[4] = new Option('Select', 0);
    newContactSelect.options[4].selected = 'selected';

   }

   function enableNewContact() {
    document.getElementById('disable-new-contact-button').style.display = "block";
    document.getElementById('new-description').style.display = "block";
    document.getElementById('new-value').style.display = "block";
    document.getElementById('new-notes').style.display = "block";
    document.getElementById('new-notify').style.display = "block";
    var newContactSelect = document.getElementById("new-contact-select");
    newContactSelect.remove(5);
   }
</script>

<!-- Page Header -->
<?= $page->showTitle() ?>
<?= $page->showBreadcrumbs() ?>
<?= $page->showMessages() ?>
<!-- End Page Header -->

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account" method="POST">
  <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
  <input type="hidden" name="target" value="<?= $target ?>" />
  <input type="hidden" name="customer_id" value="<?= $customer_id ?>" />
  <input type="hidden" name="login" value="<?= $customer->login ?>" />

  <section id="form-message">
  <ul class="connectBorder infoText"><li>Make changes and click 'Apply' to complete.</li></ul>
  </section>
   
   <!-- ============================================== -->
   <!-- CUSTOMER LOGIN -->
   <!-- ============================================== -->
  <div id="contact-main-table" class="tableBody min-tablet" style="font-size: 1.11rem;">
    <div class="tableRow" style="background: var(--color-light-one);">
      <div class="tableCell" style="width: 50%;">Login: <span class="value"><?= $customer->login ?></span></div>
      <div class="tableCell" style="width: 50%;">Type:
      <select name="automation" class="value input">
        <option value="0" <?php if ($customer->human()) print " selected"; ?>>Human</option>
        <option value="1" <?php if ($customer->automation()) print " selected"; ?>>Automation</option>
      </select>
      </div>
    </div>
  </div>
   
   <!-- ============================================== -->
   <!-- LOGIN SPECIFICS -->
   <!-- ============================================== -->
  <div class="tableBody clean min-tablet">
    <div class="tableRowHeader">
      <div class="tableCell" style="width: 30%;">Organization</div>
      <div class="tableCell" style="width: 20%;">First Name</div>
      <div class="tableCell" style="width: 20%;">Last Name</div>
      <div class="tableCell" style="width: 30%;">Time Zone</div>
    </div>
    <div class="tableRow">
      <div class="tableCell">
        <select class="value input registerValue" name="organization_id">
          <option value="">Select</option>
          <?php foreach ($organizations as $organization) { ?>
            <option value="<?= $organization->id ?>" <?php if ($organization->id == $customer->organization()->id) print " selected"; ?>><?= $organization->name ?></option>
          <?php } ?>
        </select>
      </div>
      <div class="tableCell"><input type="text" class="value input registerValue registerFirstNameValue" name="first_name" value="<?= htmlentities($customer->first_name) ?>" /></div>
      <div class="tableCell"><input type="text" class="value registerValue registerLastNameValue" name="last_name" value="<?= htmlentities($customer->last_name) ?>" /></div>
      <div class="tableCell">
        <select id="timezone" name="timezone" class="value input collectionField">
          <?php foreach (timezone_identifiers_list() as $timezone) {
            if (isset($customer->timezone))
                $selected_timezone = $customer->timezone;
            else
                $selected_timezone = 'UTC';
            ?>
            <option value="<?= $timezone ?>" <?php if ($timezone == $selected_timezone) print " selected"; ?>><?= $timezone ?></option>
          <?php } ?>
        </select>
      </div>
    </div>
  </div>
   
   <!-- ============================================== -->
   <!-- METHODS OF CONTACT -->
   <!-- ============================================== -->
  <h3>Methods of Contact</h3>
  <?php if ($customer->auth_method == 'local') { ?>
    <h3 class="marginTop_20">Change Password</h3>
    <section id="form-message">
      <ul class="connectBorder infoText"><li>Leave both fields empty for your password to stay the same.</li></ul>
    </section>
    <div class="tableBody clean min-tablet">
      <div class="tableRowHeader">
        <div class="tableCell" style="width: 30%;">New Password</div>
        <div class="tableCell" style="width: 30%;">Confirm New Password</div>
      </div>
      <div class="tableRow">
        <div class="tableCell"><input type="password" class="value wide_100per" name="password" /></div>
        <div class="tableCell"><input type="password" class="value wide_100per" name="password_2" /></div>
        <div class="tableCell"></div>
      </div>
    </div>
   <?php } ?>
   
   <!-- ============================================== -->
   <!-- CHANGE PASSWORD -->
   <!-- ============================================== -->
   <?php if ($customer->auth_method == 'local') { ?>
    <h3 class="marginTop_20">Change Password</h3>
    <section id="form-message">
      <ul class="connectBorder infoText"><li>Leave both fields empty for your password to stay the same.</li></ul>
    </section>
    <div class="tableBody clean min-tablet">
      <div class="tableRowHeader">
        <div class="tableCell" style="width: 30%;">New Password</div>
        <div class="tableCell" style="width: 30%;">Confirm New Password</div>
      </div>
      <div class="tableRow">
        <div class="tableCell"><input type="password" class="value wide_100per" name="password" /></div>
        <div class="tableCell"><input type="password" class="value wide_100per" name="password_2" /></div>
        <div class="tableCell"></div>
      </div>
    </div>
   <?php } ?>

<!-- ============================================== -->
<!-- STATUS -->
<!-- ============================================== -->
   <h3>Status</h3>
   <select class="input" name="status">
      <?php foreach (array('NEW', 'ACTIVE', 'EXPIRED', 'HIDDEN', 'DELETED', 'BLOCKED') as $status) { ?>
         <option value="<?= $status ?>" <?php if ($status == $customer->status) print " selected"; ?>><?= $status ?></option>
      <?php } ?>
   </select>

<!-- ============================================== -->
<!-- ASSIGNED ROLES OPT 1 -->
<!-- ============================================== -->
    <h3>Assigned Roles</h3>
    <table cellpadding="0" cellspacing="0" class="body">
      <tr>
        <th class="label" style="width: 10%;">&nbsp;</th>
        <th class="label" style="width: 25%;">Name</th>
        <th class="label" style="width: 65%;">Description</th>
      </tr>
      <?php
        $greenbar = '';
        foreach ($all_roles as $role) {
          if ($greenbar)
            $greenbar = '';
          else
            $greenbar = ' greenbar';
          ?>
        <tr>
          <td class="value<?= $greenbar ?>"><input type="checkbox" name="role[<?= $role->id ?>]" value="1" <?php if ($customer->has_role($role->name)) print " CHECKED"; ?> /></td>
          <td class="value<?= $greenbar ?>"><?= $role->name ?></td>
          <td class="value<?= $greenbar ?>"><?= $role->description ?></td>
        </tr>
      <?php } ?>
    </table>

<!-- ============================================== -->
<!-- ASSIGNED ROLES OPT 2 -->
<!-- ============================================== -->
    <h3>Assigned Roles</h3>
    <div class="tableBody min-tablet">
      <div class="tableRowHeader">
        <div class="tableCell" style="width: 20%;">Select</div>
        <div class="tableCell" style="width: 25%;">Name</div>
        <div class="tableCell" style="width: 30%;">Description</div>
      </div>
      <?php foreach ($contacts as $contact) { ?>
      <div class="tableRow">
        <div class="tableCell"><input type="checkbox" name="role[<?= $role->id ?>]" value="1" <?php if ($customer->has_role($role->name)) print " CHECKED"; ?> /></div>
        <div class="tableCell"><?= $role->name ?></div>
        <div class="tableCell"><?= $role->description ?></div>
      </div>
      <?php } ?>
    </div>

<!-- ============================================== -->
<!-- AUTH FAILURES -->
<!-- ============================================== -->
   <h3>Recent Auth Failures</h3>

    <section id="form-message">
    <ul class="connectBorder infoText"><li>Auth Failures Since Last Success: <?= $customer->auth_failures ?></li></ul>
    </section>

   <div id="auth-failures-table">
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell">Date</div>
            <div class="tableCell">IP Address</div>
            <div class="tableCell">Reason</div>
            <div class="tableCell">Endpoint</div>
         </div>
         <?php foreach ($authFailures as $authFailure) { ?>
            <div class="tableRow">
               <div class="tableCell"><?= $authFailure->date ?></div>
               <div class="tableCell"><?= $authFailure->ip_address ?></div>
               <div class="tableCell"><?= $authFailure->reason ?></div>
               <div class="tableCell"><?= $authFailure->endpoint ?></div>
            </div>
         <?php } ?>
      </div>
      <input type="submit" name="btnResetFailures" value="Reset Failures" />
   </div>

   <h3>Terms of Use History</h3>
   <div id="terms-of-use-table" style="margin-bottom: 180px;">
      <div class="tableBody min-tablet">
         <div class="tableRowHeader">
            <div class="tableCell">Code</div>
            <div class="tableCell">Name</div>
            <div class="tableCell">Description</div>
            <div class="tableCell">Last Action</div>
            <div class="tableCell">Last Action Date</div>
         </div>
         <?php foreach ($terms as $term) { ?>
            <div class="tableRow">
               <div class="tableCell"><?= $term->code ?></div>
               <div class="tableCell"><?= $term->name ?></div>
               <div class="tableCell"><?= $term->description ?></div>
               <div class="tableCell">
                  <?php
                  $mostRecentAction = $termsOfUseActionList->find(array('user_id' => $customer->id, 'version_id' => $term->id, 'sort' => 'date_action', 'order' => 'DESC', 'limit' => 1));
                  $mostRecentAction = array_pop($mostRecentAction);
                  print $mostRecentAction->type;
                  ?>
               </div>
               <div class="tableCell"><?=date('m/d/Y', strtotime($mostRecentAction->date_action))?></div>
            </div>
         <?php } ?>
      </div>
   </div>
   <!-- entire page button submit -->
   <div id="submit-button-container" class="tableBody min-tablet">
      <div class="tableRow button-bar">
         <input id="btn_submit" type="submit" name="method" class="button" value="Apply" onclick="return submitForm();" />
      </div>
   </div>
</form>

<!-- hidden for for "delete contact" -->
<form id="delete-contact" name="delete-contact" action="<?= PATH ?>/_register/admin_account" method="post">
   <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
   <input type="hidden" id="submit-type" name="submit-type" value="delete-contact" />
   <input type="hidden" id="register-contacts-id" name="register-contacts-id" value="" />
</form>
