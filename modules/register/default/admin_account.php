<style>
  #submit-button-container {
    bottom: 0px;
    left: 10%;
  }
</style>

<link href="/css/upload.css" type="text/css" rel="stylesheet">

<script type="text/javascript">
  function highlightImage(id) {

    // Remove highlight from all images
    var images = document.getElementsByClassName('image-item');
    for (var i = 0; i < images.length; i++) {
      images[i].classList.remove('highlighted');
    }

    // Add highlight to the clicked image
    document.getElementById('ItemImageDiv_' + id).classList.add('highlighted');
  }

  function updateDefaultImage(imageId) {
    document.getElementById('default_image_id').value = imageId;
    document.getElementById('updateImage').value = 'true';
    document.getElementById('btn_submit').click();
  }

  function removeSearchTagById(id) {
    document.getElementById('removeSearchTagId').value = id;
    submitForm();
  }


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
</script>

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

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account?customer_id=<?= $customer_id ?>" method="POST">

  <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
  <input type="hidden" name="target" value="<?= $target ?>" />
  <input type="hidden" name="customer_id" value="<?= $customer_id ?>" />
  <input type="hidden" name="login" value="<?= $customer->code ?>" />
  <input type="hidden" id="removeSearchTagId" name="removeSearchTagId" value="" />

  <input type="hidden" name="deleteImage" id="deleteImage" value="" />
  <input type="hidden" id="default_image_id" name="default_image_id" value="" />
  <input type="hidden" id="updateImage" name="updateImage" value="" />

  <section id="form-message">
    <ul class="connectBorder infoText">
      <li>Make changes and click 'Apply' to complete.</li>
    </ul>
  </section>

  <!-- ============================================== -->
  <!-- CUSTOMER LOGIN -->
  <!-- ============================================== -->
  <div id="contact-main-table" class="tableBody min-tablet" style="font-size: 1.11rem;">
    <div class="tableRowHeader">
      <div class="tableCell" style="width: 30%;">Login / Registration</div>
      <div class="tableCell" style="width: 20%;">Type</div>
    </div>
    <div class="tableRow" style="background: var(--color-light-one);">
      <div class="tableCell" style="width: 50%;">Login: <span class="value"><?= $customer->code ?></span></div>
      <div class="tableCell" style="width: 50%;">Type:
        <select name="automation" class="value input">
          <option value="0" <?php if ($customer->human()) print " selected"; ?>>Human</option>
          <option value="1" <?php if ($customer->automation()) print " selected"; ?>>Automation</option>
        </select>
      </div>
    </div>
    <div class="tableRow">
      <div class="tableCell" style="width: 50%;">
        <label for="status">Status:</label>
        <span id="status" class="value"><?= $queuedCustomer->status ?></span>
        <?php
        if ($queuedCustomer->status == "VERIFYING") {
        ?><br />
          <input type="submit" name="method" value="Resend Email" class="button submitButton registerSubmitButton" />
        <?php
        }
        ?>
      </div>
      <?php if (defined('USE_OTP') && USE_OTP) { ?>
        <div class="tableCell" style="width: 50%;">Time Based Password [Google Authenticator]
          <input id="time_based_password" type="checkbox" name="time_based_password" value="1" <?php if (!empty($customer->time_based_password)) echo "checked"; ?> <?php 
            $roles = $customer->roles();
            $requiresTOTP = false;
            $rolesRequiringTOTP = [];
            foreach ($roles as $role) {
              if ($role->time_based_password) {
                $requiresTOTP = true;
                $rolesRequiringTOTP[] = $role->name;
              }
            }
            if ($requiresTOTP || $customer->organization()->time_based_password) echo "disabled checked"; 
          ?>>
          <?php if ($requiresTOTP) { ?>
            <div class="note"><em>TOTP is required by the following roles: <?= implode(', ', $rolesRequiringTOTP) ?></em></div>
          <?php } elseif ($customer->organization()->time_based_password) { ?>
            <div class="note"><em>TOTP is required by the organization: <?= $customer->organization()->name ?></em></div>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
    <div class="tableRow">
      <div class="tableCell">
        <div>Profile Visibility</div>
        <label>
          <input type="radio" name="profile" value="public" <?php if ($customer->profile == "public") print "checked"; ?>>
          <span>
            <!-- Public Icon SVG (Open Eye) -->
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
              <path d="M15 12c0 1.654-1.346 3-3 3s-3-1.346-3-3 1.346-3 3-3 3 1.346 3 3zm9-.449s-4.252 8.449-11.985 8.449c-7.18 0-12.015-8.449-12.015-8.449s4.446-7.551 12.015-7.551c7.694 0 11.985 7.551 11.985 7.551zm-7 .449c0-2.757-2.243-5-5-5s-5 2.243-5 5 2.243 5 5 5 5-2.243 5-5z" />
            </svg>
            Profile Public
          </span>
        </label>
        <label>
          <input type="radio" name="profile" value="private" <?php if ($customer->profile == "private") print "checked"; ?>>
          <span>
            <!-- Private Icon SVG (Eye with Cross) -->
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
              <path d="M19.604 2.562l-3.346 3.137c-1.27-.428-2.686-.699-4.243-.699-7.569 0-12.015 6.551-12.015 6.551s1.928 2.951 5.146 5.138l-2.911 2.909 1.414 1.414 17.37-17.035-1.415-1.415zm-6.016 5.779c-3.288-1.453-6.681 1.908-5.265 5.206l-1.726 1.707c-1.814-1.16-3.225-2.65-4.06-3.66 1.493-1.648 4.817-4.594 9.478-4.594.927 0 1.796.119 2.61.315l-1.037 1.026zm-2.883 7.431l5.09-4.993c1.017 3.111-2.003 6.067-5.09 4.993zm13.295-4.221s-4.252 7.449-11.985 7.449c-1.379 0-2.662-.291-3.851-.737l1.614-1.583c.715.193 1.458.32 2.237.32 4.791 0 8.104-3.527 9.504-5.364-.729-.822-1.956-1.99-3.587-2.952l1.489-1.46c2.982 1.9 4.579 4.327 4.579 4.327z" />
            </svg>
            Profile Private
          </span>
        </label>
      </div>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- LOGIN SPECIFICS -->
  <!-- ============================================== -->
  <section class="tableBody clean">
    <div class="tableRowHeader">
      <div class="tableCell" style="width: 30%;">Organization</div>
      <div class="tableCell" style="width: 20%;">First Name</div>
      <div class="tableCell" style="width: 20%;">Last Name</div>
      <div class="tableCell" style="width: 30%;">Time Zone</div>
    </div>
    <div class="tableRow">
      <div class="tableCell">
        <label class="hiddenDesktop value">Organization: </label>
        <select class="value input registerValue" name="organization_id">
          <option value="">Select</option>
          <?php foreach ($organizations as $organization) { ?>
            <option value="<?= $organization->id ?>" <?php if ($organization->id == $customer->organization()->id) print " selected"; ?>><?= $organization->name ?></option>
          <?php } ?>
        </select>
      </div>
      <div class="tableCell">
        <label class="hiddenDesktop value">First Name: </label>
        <input type="text" class="value input registerValue registerFirstNameValue" name="first_name" value="<?= htmlentities($customer->first_name) ?>" />
      </div>
      <div class="tableCell">
        <label class="hiddenDesktop value">Last Name: </label>
        <input type="text" class="value registerValue registerLastNameValue" name="last_name" value="<?= htmlentities($customer->last_name) ?>" />
      </div>
      <div class="tableCell">
        <label class="hiddenDesktop value">Time Zone: </label>
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
    <div class="tableRow">
      <div class="tableCell">
        <label for="job_title">Job Title:</label>
        <input type="text" name="job_title" class="value input" value="<?= htmlentities($customer->getMetadata('job_title')) ?>" />
      </div>
      <div class="tableCell">
        <label for="job_description">Job Description:</label>
        <textarea name="job_description" style="max-height: 50px; min-height: 50px;" class="value input wide_100per"><?= htmlentities($customer->getMetadata('job_description')) ?></textarea>
      </div>
      <div class="tableCell"></div>
      <div class="tableCell"></div>
    </div>
  </section>

  <!-- ============================================== -->
  <!-- METHODS OF CONTACT -->
  <!-- ============================================== -->
  <h3>Methods of Contact</h3>
  <div id="contact-main-table" class="tableBody min-tablet">
    <div class="tableRowHeader">
      <div class="tableCell" style="width: 20%;">Type</div>
      <div class="tableCell" style="width: 25%;">Description</div>
      <div class="tableCell" style="width: 25%;">Address/Number or Email etc.</div>
      <div class="tableCell" style="width: 10%;">Notes</div>
      <div class="tableCell" style="width: 5%;">Notify</div>
      <div class="tableCell" style="width: 5%;">Public</div>
      <div class="tableCell" style="width: 5%;">Drop</div>
    </div>
    <?php foreach ($contacts as $contact) { ?>
      <div class="tableRow">
        <div class="tableCell">
          <label class="hiddenDesktop value">Type: </label>
          <select class="value input" name="type[<?= $contact->id ?>]">
            <?php foreach (array_keys($contact_types) as $contact_type) { ?>
              <option value="<?= $contact_type ?>" <?php if ($contact_type == $contact->type) print " selected"; ?>><?= $contact_types[$contact_type] ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="tableCell"><input type="text" name="description[<?= $contact->id ?>]" class="value wide_100per" value="<?= htmlentities($contact->description) ?>" /></div>
        <div class="tableCell"><input type="text" name="value[<?= $contact->id ?>]" class="value wide_100per" value="<?= htmlentities($contact->value) ?>" /></div>
        <div class="tableCell"><input type="text" name="notes[<?= $contact->id ?>]" class="value wide_100per" value="<?= htmlentities($contact->notes) ?>" /></div>
        <div class="tableCell"><input type="checkbox" name="notify[<?= $contact->id ?>]" value="1" <?php if ($contact->notify) print "checked"; ?> /></div>
        <div class="tableCell"><input type="checkbox" name="public[<?= $contact->id ?>]" value="1" <?php if ($contact->public) print "checked"; ?> /></div>
        <div class="tableCell"><img style="max-width: 18px; cursor:pointer;" name="drop_contact[<?= $contact->id ?>]" class="icon-button" src="/img/icons/icon_tools_trash_active.svg" onclick="submitDelete(<?= $contact->id ?>)" /></div>
      </div>
      <!-- New contact entry -->
    <?php } ?>
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
      <div class="tableCell"><br /><input type="text" id="new-description" name="description[0]" class="value wide_100per" style="display:none;" /></div>
      <div class="tableCell"><br /><input type="text" id="new-value" name="value[0]" class="value wide_100per" style="display:none;" /></div>
      <div class="tableCell"><br /><input type="text" id="new-notes" name="notes[0]" class="value wide_100per" style="display:none;" /></div>
      <div class="tableCell"><br /><input type="checkbox" id="new-notify" name="notify[0]" value="1" style="display:none;" /></div>
      <div class="tableCell"><br /><input type="checkbox" id="new-public" name="public[0]" value="1" style="display:none;" /></div>
      <div class="tableCell"></div>
    </div>
  </div>
  <!--	END Methods of Contact -->

  <?php if ($customer->profile == "public") { ?>
  <!-- Business Card Preview Link -->
  <div style="margin: 20px 0;">
    <a href="/_register/businesscard?customer_id=<?= $customer_id ?>" class="button" target="_blank">Preview Business Card</a>
  </div>
  <?php } ?>

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
        <div class="tableCell" style="width: 30%;">New Password</div>
        <div class="tableCell" style="width: 30%;">Confirm New Password</div>
      </div>
      <div class="tableRow">
        <div class="tableCell"><input type="password" class="value wide_100per" name="password" /></div>
        <div class="tableCell"><input type="password" class="value wide_100per" name="password_2" /></div>
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
  <!-- ASSIGNED ROLES -->
  <!-- ============================================== -->
  <h3>Assigned Roles</h3>
  <div class="tableBody min-tablet">
    <div class="tableRowHeader">
      <div class="tableCell" style="width: 20%;">Select</div>
      <div class="tableCell" style="width: 25%;">Name</div>
      <div class="tableCell" style="width: 30%;">Description</div>
    </div>
    <?php foreach ($all_roles as $role) { ?>
      <div class="tableRow">
        <div class="tableCell"><input type="checkbox" name="role[<?= $role->id ?>]" value="1" <?php if ($customer->has_role($role->name)) print " CHECKED"; ?> /></div>
        <div class="tableCell"><?= $role->name ?></div>
        <div class="tableCell"><?= strip_tags($role->description) ?></div>
      </div>
    <?php } ?>
  </div>

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
    <input type="button" name="btnAuditLog" value="Audit Log" onclick="location.href='/_register/audit_log?user_id=<?= $customer->id ?>';" />
  </div>

  <h3>Terms of Use History</h3>
  <div id="terms-of-use-table" style="margin-bottom: 10px;">
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
      <?php } ?>
    </div>
  </div>

  <h3>Locations</h3>
  <div class="table" style="width: 80%;">
    <div class="tableRowHeader">
      <div class="tableCell" style="width: 20%;">Name</div>
      <div class="tableCell" style="width: 80%;">Address</div>
    </div>
    <?php foreach ($locations as $location) { ?>
      <div class="tableRow">
        <div class="tableCell" style="width: 20%;"><?= $location->name ?></div>
        <div class="tableCell" style="width: 80%;"><?= $location->HTMLBlockFormat() ?></div>
      </div>
    <?php   } ?>
  </div>

  <br /><br />
  <h3 style="display:inline;">Customer Search Tags</h3>
  <h4 style="display:inline;">(customer support knowledge center)</h4>
  <div class="tableBody min-tablet">
    <div class="tableRowHeader">
      <div class="tableCell" style="width: 33%;">&nbsp;</div>
      <div class="tableCell" style="width: 33%;">Category</div>
      <div class="tableCell" style="width: 33%;">Search Tag</div>
    </div>
    <?php
    foreach ($registerCustomerSearchTags as $searchTag) {
    ?>
      <div class="tableRow">
        <div class="tableCell">
          <input type="button" onclick="removeSearchTagById('<?= $searchTag->id ?>')" name="removeSearchTag" value="Remove" class="button" />
        </div>
        <div class="tableCell">
          <?= $searchTag->category ?>
        </div>
        <div class="tableCell">
          <?= $searchTag->value ?>
        </div>

      </div>

    <?php
    }
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

  <div class="input-horiz" id="itemImages">
    <h3 class="label align-top">User Images</h3><br />
    <?php
    $defaultImageId = $customer->getMetadata('default_image');
    $hasImages = false;
    if ($defaultImageId) {
      $defaultImage = new \Storage\File($defaultImageId);
      if ($defaultImage->id) {
        $hasImages = true;
    ?>
        <div class="image-container">
          <h4>Current Default Image</h4>
          <img src="/_storage/downloadfile?file_id=<?= $defaultImageId ?>" style="max-width: 200px;" />
          <p><?= htmlspecialchars($defaultImage->name) ?></p>
        </div>
    <?php
      }
    }
    ?>
    <h2>Click to select your default user image</h2>
    <div class="images-container">
      <?php
      if (empty($customerImages)) {
        if (!$hasImages) {
      ?>
          <h4 class="no-images-found">No images found for this user.</h4>
        <?php
        }
      } else {
        foreach ($customerImages as $image) {
          $hasImages = true;
        ?>
          <div class="image-item" id="ItemImageDiv_<?= $image->id ?>" onclick="highlightImage('<?= $image->id ?>'); updateDefaultImage('<?= $image->id ?>');">
            <img src="/_storage/downloadfile?file_id=<?= $image->id ?>" style="max-width: 100px;" />
            <p><?= htmlspecialchars($image->name) ?></p>
            <?php if ($defaultImageId == $image->id): ?>
              <span class="image-indicator">Default</span>
            <?php endif; ?>
          </div>
      <?php
        }
      }
      ?>
    </div>
  </div>

  <!-- Backup Codes Section -->
  <h3>Backup Codes</h3>
  <div class="tableBody min-tablet">
    <form method="post" action="/_register/admin_account?customer_id=<?= $customer_id ?>">
      <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
      <input type="hidden" name="generate_backup_codes" value="1">
      <p><strong>Generate 6 backup codes for this user. Generating new codes will erase all previous backup codes.</strong></p>
      <input type="submit" class="button" value="Generate Backup Codes">
    </form>
    <?php if (isset($generatedBackupCodes) && is_array($generatedBackupCodes)) { ?>
      <div class="backup-codes-list" style="margin-top: 10px;">
        <p><strong>Backup Codes (save these in a safe place):</strong></p>
        <ul style="font-size: 1.2em; letter-spacing: 2px;">
          <?php foreach ($generatedBackupCodes as $code) { ?>
            <li><?= htmlentities($code) ?></li>
          <?php } ?>
        </ul>
      </div>
    <?php } ?>
    <?php if (isset($allBackupCodes) && count($allBackupCodes) > 0) { ?>
      <div class="backup-codes-list" style="margin-top: 10px;">
        <p><strong>Current Backup Codes:</strong></p>
        <table style="width: 100%; max-width: 400px; border-collapse: collapse;">
          <tr><th>Code</th><th>Status</th></tr>
          <?php foreach ($allBackupCodes as $bcode) { ?>
            <tr>
              <td style="padding: 4px 8px; font-family: monospace; font-size: 1.1em;">
                <?= htmlentities($bcode['code']) ?>
              </td>
              <td style="padding: 4px 8px;">
                <?php if ($bcode['used']) { ?>
                  <span style="color: #b00; font-weight: bold;">Used</span>
                <?php } else { ?>
                  <span style="color: #080; font-weight: bold;">Unused</span>
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
        </table>
      </div>
    <?php } ?>
  </div>
  <!-- End Backup Codes Section -->

  <!-- entire page button submit -->
  <div id="submit-button-container" class="tableBody min-tablet">
    <div class="tableRow button-bar">
      <input id="btn_submit" type="submit" name="method" class="button" value="Apply" onclick="return submitForm();" />
    </div>
  </div>
</form>

<!-- hidden for for "delete contact" -->
<form id="delete-contact" name="delete-contact" action="<?= PATH ?>/_register/admin_account?customer_id=<?= $customer_id ?>" method="post">
  <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
  <input type="hidden" id="submit-type" name="submit-type" value="delete-contact" />
  <input type="hidden" id="register-contacts-id" name="register-contacts-id" value="" />
</form>

<?php if ($repository->id) { ?>
  <form name="repoUpload" action="/_register/admin_account?customer_id=<?= $customer->id ?>" method="post" enctype="multipart/form-data">
    <div class="container">
      <h3 class="label">Upload User Image for this account</h3>
      <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
      <input type="hidden" name="repository_id" value="<?= $repository->id ?>" />
      <input type="file" name="uploadFile" />
      <input type="submit" name="btn_submit" class="button" value="Upload" />
    </div>
  </form>
<?php    } else {
?>
  <div class="container">
    <h3 class="label">Upload User Image for this account</h3>
    <p style="color: red;">Repository not found. (please create an S3, Local, Google or Dropbox repository to upload images for this user)</p>
  </div>
<?php
}
?>
