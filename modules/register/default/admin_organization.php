<script language="JavaScript">
	function addLocation() {
		var organization_id = document.forms[0].organization_id.value;
		window.location.href = "/_register/admin_location?organization_id="+organization_id;
		return true;
	}
	
	function showHidden() {
		var organization_id = document.forms[0].organization_id.value;
		window.location.href = "/_register/admin_organization?organization_id="+organization_id+"&showAllUsers=<?=(isset($_REQUEST['showAllUsers']) && !empty($_REQUEST['showAllUsers'])) ? '0' : '1'?>";
		return true;
	}
	
	function submitDefaultLocation(type, value) {
		var organization_id = document.forms[0].organization_id.value;
		window.location.href = "/_register/admin_organization?organization_id="+organization_id+"&" + type + "=" + value;
		return true;
	}
	
	// remove an organization tag by id
	function removeTagById(id) {
	    document.getElementById('removeTagId').value = id;
	    document.getElementById('orgDetails').submit();
	}
</script>

<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<?php $activeTab = 'details'; ?>
<?php
    // Show organization info container similar to product container
    $title = htmlspecialchars($organization->name ?: $organization->code);
?>
<div class="product-container">
    <div class="product-title"><?=$title?></div>
</div>
<?php
?>
<div class="tabs">
    <a href="/_register/admin_organization/<?= $organization->code ?>" class="tab <?= $activeTab==='details'?'active':'' ?>">Details</a>
    <a href="/_register/admin_organization_users/<?= $organization->code ?>" class="tab <?= $activeTab==='users'?'active':'' ?>">Users</a>
    <a href="/_register/admin_organization_tags/<?= $organization->code ?>" class="tab <?= $activeTab==='tags'?'active':'' ?>">Tags</a>
    <a href="/_register/admin_organization_locations/<?= $organization->code ?>" class="tab <?= $activeTab==='locations'?'active':'' ?>">Locations</a>
    <a href="/_register/admin_organization_audit_log/<?= $organization->code ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
</div>

<form id="orgDetails" name="orgDetails" method="POST">

    <input type="hidden" name="organization_id" value="<?=$organization->id?>"/>
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <input type="hidden" id="removeTagId" name="removeTagId" value=""/>
    
    <div class="form_instruction">Make changes and click 'Apply' to complete.</div>

    <!--	Start First Row-->
    <div class="tableBody min-tablet marginTop_20">
	    <div class="tableRowHeader">
		    <div class="tableCell width-20per">Code</div>
		    <div class="tableCell width-20per">Name</div>
		    <div class="tableCell width-15per">Status</div>
		    <div class="tableCell width-10per">Can Resell</div>
		    <div class="tableCell width-15per">Reseller</div>
		    <div class="tableCell width-15per">Password Exp. (days)</div>
	    </div> <!-- end row header -->
	    <div class="tableRow">
		    <div class="tableCell">
			    <input name="code" type="text" id="organizationCodeValue" class="width-100per" value="<?=$organization->code?>" />
		    </div>
		    <div class="tableCell">
			    <input name="name" type="text" id="organizationNameValue" class="width-100per" value="<?=$organization->name?>" />
		    </div>
		    <div class="tableCell">
			    <select name="status" id="organizationStatusValue" class="width-100per">
				    <?php		foreach ($statii as $status) { ?>
				    <option value="<?=$status?>"<?php	if ($status == $organization->status) print " selected"; ?>><?=$status?></option>
				    <?php		} ?>
			    </select>
		    </div>
		    <div class="tableCell">
			    <input name="is_reseller" type="checkbox" value="1" <?php	if($organization->is_reseller) print " checked"?> />
		    </div>
		    <div class="tableCell">
			    <select name="assigned_reseller_id" class="width-100per">
				    <option value="">Select</option>
				    <?php	
				    foreach ($resellers as $reseller) {
				        if ($organization->id == $reseller->id) continue;
				    ?>
				        <option value="<?=$reseller->id?>"<?php	if($organization->assigned_reseller_id == $reseller->id) print " selected";?>><?=$reseller->name?></option>
				    <?php
				    } 
				    ?>
			    </select>
		    </div>
		    <div class="tableCell">
			    <input name="password_expiration_days" type="number" step="1" min="0" max="365" id="password_expiration_days" class="width-100per" value="<?=$organization->password_expiration_days?>" />
		    </div>
	    </div>
    </div>
    <div class="tableBody">
	    <div class="tableRowHeader">
			<div class="tableCell">Is A Customer</div>
			<div class="tableCell">Is A Vendor</div>
			<div class="tableCell">Account Number</div>
			<div class="tableCell">Website URL</div>
		    <div class="tableCell">Require Two-Factor Authentication</div>
	    </div> <!-- end row header -->
	    <div class="tableRow">
			<div class="tableCell">
			    <input name="is_customer" type="checkbox" value="1" <?php if ($organization->is_customer) print " checked"?>>
		    </div>
			<div class="tableCell">
			    <input name="is_vendor" type="checkbox" value="1" <?php if ($organization->is_vendor) print " checked"?>>
		    </div>
			<div class="tableCell">
			    <input name="account_number" type="text" class="width-100per" value="<?=$organization->account_number?>" placeholder="Enter account number"/>
		    </div>
			<div class="tableCell">
			    <input id="website_url" name="website_url" class="width-250px" placeholder="http://" value="<?=$organization->website_url?>"/>
		    </div>
		    <div class="tableCell">
			    <input name="time_based_password" type="checkbox" value="1" <?php if($organization->time_based_password) print " checked"?>>
			    <label for="time_based_password">Require two-factor authentication for all users in this organization</label>
		    </div>
	    </div>
    </div>
    <div class="tableBody">
	    <div class="tableRowHeader">
		    <div class="tableCell width-100per">Notes</div>
	    </div> <!-- end row header -->
	    <div class="tableRow">
		    <div class="tableCell">
			    <textarea name="notes" class="width-250px"><?=strip_tags($organization->notes)?></textarea>
		    </div>
	    </div>
    </div>
    <div><input type="submit" name="method" value="Apply" class="button"/></div>

    <!--End first row-->
</form>
