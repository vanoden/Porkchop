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

<?php $activeTab = 'login'; ?>

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

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account?customer_id=<?= $customer_id ?>" method="POST">

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
  <!-- CUSTOMER LOGIN -->
  <!-- ============================================== -->
  <div id="contact-main-table" class="tableBody min-tablet table-main">
    <div class="tableRowHeader">
      <div class="tableCell width-30per">Login / Registration</div>
      <div class="tableCell width-20per">Type</div>
    </div>
    <div class="tableRow register-admin-account-light-background">
      <div class="tableCell width-50per">Login: <span class="value"><?= $customer->code ?></span></div>
      <div class="tableCell width-50per">Type:
        <select name="automation" class="value input">
          <option value="0" <?php if ($customer->human()) print " selected"; ?>>Human</option>
          <option value="1" <?php if ($customer->automation()) print " selected"; ?>>Automation</option>
        </select>
      </div>
    </div>
    <div class="tableRow">
        <div class="tableCell width-50per">Account Status
			<select class="input" name="status">
<?php foreach (array('NEW', 'ACTIVE', 'EXPIRED', 'HIDDEN', 'DELETED', 'BLOCKED') as $status) { ?>
				<option value="<?= $status ?>" <?php if ($status == $customer->status) print " selected"; ?>><?= $status ?></option>
<?php } ?>
			</select>
		</div>
	
		<div class="tableCell width-50per">
			<label for="status">Registration Status:</label>
			<span id="status" class="value"><?= $registration_status ?></span>
<?php
	if ($registration_status == "VERIFYING") { ?>
			<br />
			<input type="submit" name="method" value="Resend Email" class="button submitButton registerSubmitButton" />
<?php
	}
?>
      </div>
	</div>
	<div class="tableRow">
      <?php 
		$configuration = new \Site\Configuration();
	  	if ($configuration->getValueBool("use_otp")) { ?>
        <div class="tableCell width-50per">Time Based Password [Authenticator App]
          <input id="time_based_password" type="checkbox" name="time_based_password" value="1" <?php if (!empty($customer->time_based_password)) echo "checked"; ?> <?php 
            $roles = $customer->roles();
            $requiresTOTP = false;
            $rolesRequiringTOTP = [];
            foreach ($roles as $role) {
              if ($role && isset($role->time_based_password) && $role->time_based_password) {
                $requiresTOTP = true;
                $rolesRequiringTOTP[] = $role->name;
              }
            }
            $organization = $customer->organization();
            $orgRequiresTOTP = $organization && isset($organization->time_based_password) && $organization->time_based_password;
            if ($requiresTOTP || $orgRequiresTOTP) echo "disabled checked"; 
          ?>>
          <?php if ($requiresTOTP) { ?>
            <div class="note"><em>TOTP is required by the following roles: <?= implode(', ', $rolesRequiringTOTP) ?></em></div>
          <?php } elseif ($orgRequiresTOTP) { ?>
            <div class="note"><em>TOTP is required by the organization: <?= $organization->name ?></em></div>
          <?php } ?>
        </div>
      <?php } ?>
      <div class="tableCell">
        Profile Visibility
          &nbsp;&nbsp;&nbsp;<input type="radio" name="profile" value="public" <?php if ($customer->profile == "public") print "checked"; ?>>
          <span>
            <!-- Public Icon SVG (Open Eye) -->
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
              <path d="M15 12c0 1.654-1.346 3-3 3s-3-1.346-3-3 1.346-3 3-3 3 1.346 3 3zm9-.449s-4.252 8.449-11.985 8.449c-7.18 0-12.015-8.449-12.015-8.449s4.446-7.551 12.015-7.551c7.694 0 11.985 7.551 11.985 7.551zm-7 .449c0-2.757-2.243-5-5-5s-5 2.243-5 5 2.243 5 5 5 5-2.243 5-5z" />
            </svg>
           	Public
          </span>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="profile" value="private" <?php if ($customer->profile == "private") print "checked"; ?>>
          <span>
            <!-- Private Icon SVG (Eye with Cross) -->
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
              <path d="M19.604 2.562l-3.346 3.137c-1.27-.428-2.686-.699-4.243-.699-7.569 0-12.015 6.551-12.015 6.551s1.928 2.951 5.146 5.138l-2.911 2.909 1.414 1.414 17.37-17.035-1.415-1.415zm-6.016 5.779c-3.288-1.453-6.681 1.908-5.265 5.206l-1.726 1.707c-1.814-1.16-3.225-2.65-4.06-3.66 1.493-1.648 4.817-4.594 9.478-4.594.927 0 1.796.119 2.61.315l-1.037 1.026zm-2.883 7.431l5.09-4.993c1.017 3.111-2.003 6.067-5.09 4.993zm13.295-4.221s-4.252 7.449-11.985 7.449c-1.379 0-2.662-.291-3.851-.737l1.614-1.583c.715.193 1.458.32 2.237.32 4.791 0 8.104-3.527 9.504-5.364-.729-.822-1.956-1.99-3.587-2.952l1.489-1.46c2.982 1.9 4.579 4.327 4.579 4.327z" />
            </svg>
            Private
          </span>
      </div>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- LOGIN SPECIFICS -->
  <!-- ============================================== -->
  <section class="tableBody clean">
    <div class="tableRowHeader">
      <div class="tableCell width-30per">Organization</div>
      <div class="tableCell width-20per">First Name</div>
      <div class="tableCell width-20per">Last Name</div>
      <div class="tableCell width-30per">Time Zone</div>
    </div>
    <div class="tableRow">
      <div class="tableCell">
        <label class="display-none value">Organization: </label>
        <select class="value input registerValue" name="organization_id">
          <option value="">Select</option>
          <?php foreach ($organizations as $organization) { ?>
            <option value="<?= $organization->id ?>" <?php if ($customer->organization() && $organization->id == $customer->organization()->id) print " selected"; ?>><?= $organization->name ?></option>
          <?php } ?>
        </select>
      </div>
      <div class="tableCell">
        <label class="display-none value">First Name: </label>
        <input type="text" class="value input registerValue registerFirstNameValue" name="first_name" value="<?= htmlentities($customer->first_name) ?>" />
      </div>
      <div class="tableCell">
        <label class="display-none value">Last Name: </label>
        <input type="text" class="value registerValue registerLastNameValue" name="last_name" value="<?= htmlentities($customer->last_name) ?>" />
      </div>
      <div class="tableCell">
        <label class="display-none value">Time Zone: </label>
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
        <textarea name="job_description" class="value input width-100per register-admin-account-job-description"><?= htmlentities($customer->getMetadata('job_description')) ?></textarea>
      </div>
      <div class="tableCell"></div>
      <div class="tableCell"></div>
    </div>
  </section>


  <!-- entire page button submit -->
  <div id="submit-button-container" class="tableBody min-tablet">
    <div class="tableRow button-bar">
      <input id="btn_submit" type="submit" name="method" class="button" value="Apply" onclick="return submitForm();" />
    </div>
  </div>
</form>

<script type="text/javascript">
  // submit form
  function submitForm() {
    return true;
  }
</script>