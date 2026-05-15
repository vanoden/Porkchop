<script language="JavaScript">
	function addLocation() {
		var organization_id = document.forms[0].organization_id.value;
		window.location.href = "/_register/location?organization_id="+organization_id;
		return true;
	}

	function showHidden() {
		var organization_id = document.forms[0].organization_id.value;
		window.location.href = "/_register/organization?organization_id="+organization_id+"&showAllUsers=<?=(isset($_REQUEST['showAllUsers']) && !empty($_REQUEST['showAllUsers'])) ? '0' : '1'?>";
		return true;
	}

	function submitDefaultLocation(type, value) {
		var organization_id = document.forms[0].organization_id.value;
		window.location.href = "/_register/organization?organization_id="+organization_id+"&" + type + "=" + value;
		return true;
	}

	// remove an organization tag by id
	function removeTagById(id) {
	    document.getElementById('removeTagId').value = id;
	    document.getElementById('orgDetails').submit();
	}
</script>

<section id="org_form" class="body">
<div class="organization-page-wrapper" style="display: flex; flex-direction: column; width: 100%;">
<!-- Page Header -->
<?=$page->showSubHeading()?>
<!-- End Page Header -->

<form id="orgDetails" name="orgDetails" method="POST">

    <input type="hidden" name="organization_id" value="<?=$organization->id?>"/>
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <input type="hidden" id="removeTagId" name="removeTagId" value=""/>

    <div class="form_instruction">Make changes and click 'Apply' to complete.</div>

    <!--	Start First Row-->
    <table class="responsive-table min-tablet marginTop_20">
      <thead>
        <tr>
          <th scope="col">Code</th>
          <th scope="col">Name</th>
          <th scope="col">Status</th>
          <th scope="col">Can Resell</th>
          <th scope="col">Reseller</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td data-label="Code"><?=$organization->code?></td>
          <td data-label="Name"><?=$organization->name?></td>
          <td data-label="Status"><?=$organization->status?></td>
          <td data-label="Can Resell"><?=$organization->is_reseller ? "Yes" : "No"?></td>
          <td data-label="Reseller"><?=($organization->reseller && isset($organization->reseller->name)) ? $organization->reseller->name : ''?></td>
        </tr>
      </tbody>
    </table>

    <table class="responsive-table">
      <thead>
        <tr>
          <th scope="col">Require Two-Factor Authentication for All Users</th>
          <th scope="col">Password Expires after # Days (0 Never Expires)</th>
          <th scope="col">Website URL</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td data-label="Require Two-Factor Authentication for All Users">
            <?php	if ($can_manage) { ?>
            <input name="time_based_password" type="checkbox" value="1" <?php if($organization->time_based_password) print " checked"?> />
            <?php	} elseif ($organization->time_based_password) { ?>
            <span class="value">Yes</span>
            <?php	} else { ?>
            <span class="value">No</span>
            <?php	} ?>
          </td>

          <td data-label="Password Expires after # Days (0 Never Expires)">
            <?php	if ($can_manage) { ?>
            <input name="password_expiration_days" type="number" step="1" min="0" max="365" id="password_expiration_days" style="width: 40px; max-width: 40px" value="<?=$organization->password_expiration_days?>" />
            <?php	} else { ?>
            <span class="value"><?=$organization->password_expiration_days?></span>
            <?php	} ?>
          </td>

          <td data-label="Website URL">
            <?php	if ($can_manage) { ?>
            <input type="text" id="website_url" name="website_url" style="width: 450px; max-width: 450px;" placeholder="http://" value="<?=$organization->website_url?>"/>
            <?php	} else { ?>
            <span class="value"><?=$organization->website_url?></span>
            <?php	} ?>
          </td>
        </tr>
      </tbody>
    </table>
      
    <button type="submit" name="method" value="Apply">Submit</button>

    <div class="user_accounts_container">
      <h3>Current Users</h3>
      <label for="showAllUsers">
        <input type="checkbox" id="showAllUsers" name="showAllUsers" value="showAllUsers" onclick="showHidden()" <?= (isset($_REQUEST['showAllUsers']) && !empty($_REQUEST['showAllUsers'])) ? 'checked' : '' ?>>SHOW ALL (Expired/Hidden/Deleted)
      </label>
        
      <table class="responsive-table responsive-table--banded">
        <thead>
          <tr>
            <th scope="col">Username</th>
            <th scope="col">First Name</th>
            <th scope="col">Last Name</th>
            <th scope="col">Status</th>
            <th scope="col">Last Active</th>
            <th scope="col">Last Password Change</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($members as $member) {
            $statistics = new \Register\User\Statistics($member->id);
            if ($can_manage) $sub_url = 'org_account';
            else $sub_url = 'account';
          ?>
          <tr class="member_status_<?= strtolower($member->status) ?>">
            <td data-label="Username"><a href="/_register/<?= $sub_url ?>?customer_id=<?= $member->id ?>"><?= $member->code ?></a></td>
            <td data-label="First Name"><?= $member->first_name ?></td>
            <td data-label="Last Name"><?= $member->last_name ?></td>
            <td data-label="Status"><?= $member->status ?></td>
            <td data-label="Last Active"><?= $member->last_active() ?></td>
            <td data-label="Last Password Change"><?= $statistics?->last_password_change_date?->format('Y-m-d H:i:s') ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>

      <h3>Automation Users</h3>
      <?php	if ($organization->id) {
        if ($can_manage) $sub_url = 'org_account';
        else $sub_url = 'account';
      ?>
      <table class="responsive-table min-tablet">
        <colgroup>
          <col class="col-w-20">
          <col class="col-w-10">
          <col>
        </colgroup>
        <thead>
          <tr>
            <th scope="col">Username</th>
            <th scope="col">Status</th>
            <th scope="col">Last Active</th>
          </tr>
        </thead>
        <tbody>
          <?php	foreach ($automationMembers as $member) { ?>
          <tr class="member_status_<?=strtolower($member->status)?>">
            <td data-label="Username"><a href="/_register/<?=$sub_url?>?customer_id=<?=$member->id?>"><?=$member->code?></a></td>
            <td data-label="Status"><?=$member->status?></td>
            <td data-label="Last Active"><?=$member->last_active()?></td>
          </tr>
          <?php	} ?>
        </tbody>
      </table>
      <?php	} ?>
    </div>

    <h2>Locations</h2>
    <table class="responsive-table">
      <colgroup>
        <col class="col-w-5">
        <col class="col-w-5">
        <col class="col-w-20">
        <col class="col-w-20">
        <col class="col-w-20">
        <col>
      </colgroup>
      <thead>
        <tr>
          <th scope="col">Default Billing</th>
          <th scope="col">Default Shipping</th>
          <th scope="col">Name</th>
          <th scope="col">Address</th>
          <th scope="col">City</th>
          <th scope="col">Province/Region</th>
        </tr>
      </thead>
      <tbody>
        <?php	foreach ($locations as $location) {
          if ($can_manage) $disabled = '';
          else $disabled = 'disabled';
        ?>
        <tr>
          <td data-label="Default Billing"><input type="radio" name="default_billing_location_id" <?php if ($organization->default_billing_location_id == $location->id) echo "checked='checked'"; ?> <?=$disabled?> value="<?=$location->id?>" onclick="submitDefaultLocation('setDefaultBilling',<?=$location->id?>)"></td>
          <td data-label="Default Shipping"><input type="radio" name="default_shipping_location_id" <?php if ($organization->default_shipping_location_id == $location->id) echo "checked='checked'"; ?> <?=$disabled?> value="<?=$location->id?>" onclick="submitDefaultLocation('setDefaultShipping',<?=$location->id?>)"></td>
          <td data-label="Name"><a href="/_register/location?organization_id=<?=$organization->id?>&id=<?=$location->id?>"><?=$location->name?></a></td>
          <td data-label="Address"><?=$location->address_1?></td>
          <td data-label="City"><?=$location->city?></td>
          <td data-label="Province/Region"><?=$location->province()->name?><br><?=$location->province()->country()->name?></td>
        </tr>
        <?php	} ?>
      </tbody>
    </table>
    <button type="button" name="method" value="Add Location" onclick="addLocation()">Add Location</button>
    <!--End first row-->
</form>
</div>
</section>
