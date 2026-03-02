<script type="text/javascript" src="/js/geography.js"></script>
<script language="Javascript">
	function popProvinceSelect() {
	
		var countryID = document.forms[0].country_id.value;
		var country = Object.create(Country);
		country.id = countryID;
		var provinces = country.getProvinces();
		var selectElem = document.forms[0].province_id;
		selectElem.innerHTML = "";
		for (var i = 0; i < provinces.length; i ++) {
			var option = document.createElement('option');
			option.value = provinces[i].id;
			option.innerHTML = provinces[i].name;
			selectElem.appendChild(option);
		}
		return true;
	}
</script>

<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<?php $activeTab = 'locations'; ?>
<?php
    // Show organization info container similar to product container
    if (isset($organization)) {
        $title = htmlspecialchars($organization->name ?: $organization->code);
?>
<div class="product-container">
    <div class="product-title"><?=$title?></div>
</div>
<?php
    }
?>
<?php if (isset($organization) && $organization->id) { ?>
<div class="tabs">
    <a href="/_register/admin_organization/<?= $organization->code ?>" class="tab <?= $activeTab==='details'?'active':'' ?>">Details</a>
    <a href="/_register/admin_organization_users/<?= $organization->code ?>" class="tab <?= $activeTab==='users'?'active':'' ?>">Users</a>
    <a href="/_register/admin_organization_tags/<?= $organization->code ?>" class="tab <?= $activeTab==='tags'?'active':'' ?>">Tags</a>
    <a href="/_register/admin_organization_locations/<?= $organization->code ?>" class="tab <?= $activeTab==='locations'?'active':'' ?>">Locations</a>
    <a href="/_register/admin_organization_audit_log/<?= $organization->code ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
</div>
<?php } elseif (!empty($_REQUEST['customer_id'])) {
    $cid = (int)$_REQUEST['customer_id'];
?>
<div class="tabs">
    <a href="/_register/admin_account?customer_id=<?= $cid ?>" class="tab">Login / Registration</a>
    <a href="/_register/admin_account_contacts?customer_id=<?= $cid ?>" class="tab">Methods of Contact</a>
    <a href="/_register/admin_account_password?customer_id=<?= $cid ?>" class="tab">Change Password</a>
    <a href="/_register/admin_account_roles?customer_id=<?= $cid ?>" class="tab">Assigned Roles</a>
    <a href="/_register/admin_account_auth_failures?customer_id=<?= $cid ?>" class="tab">Recent Auth Failures</a>
    <a href="/_register/admin_account_terms?customer_id=<?= $cid ?>" class="tab">Terms of Use History</a>
    <a href="/_register/admin_account_locations?customer_id=<?= $cid ?>" class="tab active">Locations</a>
    <a href="/_register/admin_account_images?customer_id=<?= $cid ?>" class="tab">User Images</a>
    <a href="/_register/admin_account_backup_codes?customer_id=<?= $cid ?>" class="tab">Backup Codes</a>
    <a href="/_register/admin_account_search_tags?customer_id=<?= $cid ?>" class="tab">Search Tags</a>
    <a href="/_register/admin_account_audit_log?customer_id=<?= $cid ?>" class="tab">Audit Log</a>
    <a href="/_register/admin_account_register_audit?customer_id=<?= $cid ?>" class="tab">Register Audit</a>
</div>
<?php } ?>

<div class="form_instruction">Add/Edit Location</div>

<?php
if (!empty($locationReadOnly)) { ?>
<section id="form-message" class="marginBottom_10">
	<p class="value">Existing addresses cannot be edited. <a href="/_register/admin_location?organization_id=<?= isset($organization->id) ? $organization->id : '' ?>&amp;copy_id=<?= $location->id ?>">Copy this address</a> to create a new one with changes<?= (isset($organization->id) && $organization->id) ? ', or <a href="/_register/admin_organization_locations/'.htmlspecialchars($organization->code).'?organization_id='.$organization->id.'&amp;setHidden='.$location->id.'">hide this address</a> if it is no longer used' : '' ?>.</p>
</section>
<?php } ?>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>
<?php	} ?>

<form name="locationForm" method="post" action="/_register/admin_location<?php 
    $url_params = array();
    if (isset($_REQUEST['organization_id'])) $url_params[] = 'organization_id=' . $_REQUEST['organization_id'];
    if (isset($_REQUEST['id']) && $_REQUEST['id']) $url_params[] = 'id=' . $_REQUEST['id'];
    if (isset($_REQUEST['user_id'])) $url_params[] = 'user_id=' . $_REQUEST['user_id'];
    if (!empty($_REQUEST['customer_id'])) $url_params[] = 'customer_id=' . $_REQUEST['customer_id'];
    if (!empty($_REQUEST['return_for_shipment_id'])) $url_params[] = 'return_for_shipment_id=' . (int)$_REQUEST['return_for_shipment_id'];
    if (!empty($url_params)) echo '?' . implode('&', $url_params);
?>">
    <input type="hidden" name="id" value="<?= (int)$location->id ?>" />
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <?php	if (isset($_REQUEST['organization_id'])) { ?>
	    <input type="hidden" name="organization_id" value="<?=$organization->id?>" />
    <?php	}
	    if (!empty($_REQUEST['customer_id'])) { ?>
	    <input type="hidden" name="customer_id" value="<?= (int)$_REQUEST['customer_id'] ?>" />
    <?php	}
	    if (!empty($_REQUEST['return_for_shipment_id'])) { ?>
	    <input type="hidden" name="return_for_shipment_id" value="<?= (int)$_REQUEST['return_for_shipment_id'] ?>" />
    <?php	}
	    if (isset($_REQUEST['user_id'])) { ?>
    <div class="tableBody min-tablet marginTop_20">
	    <div class="tableRowHeader">
		    <div class="tableCell width-100per">Customer</div>
	    </div>
	    <div class="tableRow">
		    <div class="tableCell">
			    <span class="value"><?=$user->full_name()?></span>
			    <input type="hidden" name="user_id" value="<?=$user->id?>" />
		    </div>
	    </div>
    </div>
    <?php	} ?>
    
    <div class="tableBody min-tablet marginTop_20">
	    <div class="tableRowHeader">
		    <div class="tableCell width-50per">Name</div>
		    <div class="tableCell width-50per">Address 1</div>
	    </div>
	    <div class="tableRow">
		    <div class="tableCell">
			    <?php if ($locationReadOnly) { ?><span class="value"><?= htmlspecialchars($location->name) ?></span><?php } else { ?><input type="text" name="name" class="width-100per" value="<?= htmlspecialchars($location->name) ?>" /><?php } ?>
		    </div>
		    <div class="tableCell">
			    <?php if ($locationReadOnly) { ?><span class="value"><?= htmlspecialchars($location->address_1) ?></span><?php } else { ?><input type="text" name="address_1" class="width-100per" value="<?= htmlspecialchars($location->address_1) ?>" /><?php } ?>
		    </div>
	    </div>
    </div>
    
    <div class="tableBody">
	    <div class="tableRowHeader">
		    <div class="tableCell width-50per">Address 2</div>
		    <div class="tableCell width-50per">City</div>
	    </div>
	    <div class="tableRow">
		    <div class="tableCell">
			    <?php if ($locationReadOnly) { ?><span class="value"><?= htmlspecialchars($location->address_2) ?></span><?php } else { ?><input type="text" name="address_2" class="width-100per" value="<?= htmlspecialchars($location->address_2) ?>" /><?php } ?>
		    </div>
		    <div class="tableCell">
			    <?php if ($locationReadOnly) { ?><span class="value"><?= htmlspecialchars($location->city) ?></span><?php } else { ?><input type="text" name="city" class="width-100per" value="<?= htmlspecialchars($location->city) ?>" /><?php } ?>
		    </div>
	    </div>
    </div>
    
    <div class="tableBody">
	    <div class="tableRowHeader">
		    <div class="tableCell width-33per">Country</div>
		    <div class="tableCell width-33per">State/Province</div>
		    <div class="tableCell width-33per">Zip Code</div>
	    </div>
	    <div class="tableRow">
		    <div class="tableCell">
			    <?php if ($locationReadOnly) { ?><span class="value"><?= isset($selected_country) ? htmlspecialchars($selected_country->name) : '' ?></span><?php } else { ?><select name="country_id" class="width-100per" onchange="popProvinceSelect();">
				    <option value="">Select</option>
	    <?php	foreach ($countries as $country) { ?>
				    <option value="<?=$country->id?>"<?php	if (isset($selected_country) && $country->id == $selected_country->id) print " selected"; ?>><?=$country->name?></option>
	    <?php	} ?>
			    </select><?php } ?>
		    </div>
		    <div class="tableCell">
			    <?php if ($locationReadOnly) { ?><span class="value"><?= isset($selected_province) ? htmlspecialchars($selected_province->name) : '' ?></span><?php } else { ?><select name="province_id" class="width-100per">
	    <?php	if (isset($country->id) && $country->id > 0 && is_array($provinces)) {
				    foreach ($provinces as $province) {
	    ?>		
	            <option value="<?=$province->id?>"<?php	if (isset($selected_province) && $province->id == $selected_province->id) print " selected"; ?>><?=$province->name?></option>
	    <?php			}
			    } else { ?>
				    <option value="">Choose Country First</option>
	    <?php		} ?>
			    </select><?php } ?>
		    </div>
		    <div class="tableCell">
			    <?php if ($locationReadOnly) { ?><span class="value"><?= htmlspecialchars($location->zip_code) ?></span><?php } else { ?><input type="text" name="zip_code" class="width-100per" value="<?= htmlspecialchars($location->zip_code) ?>" /><?php } ?>
		    </div>
	    </div>
    </div>
    
    <?php
    if (isset($_REQUEST['organization_id']) && !$locationReadOnly) { 
        // Check if current location is set as default for this organization
        $isDefaultShipping = false;
        $isDefaultBilling = false;
        
        if ($location->id > 0 && isset($organization)) {
            $isDefaultShipping = ($organization->default_shipping_location_id == $location->id);
            $isDefaultBilling = ($organization->default_billing_location_id == $location->id);
        }
    ?>
    <div class="tableBody">
	    <div class="tableRowHeader">
		    <div class="tableCell width-100per">Default Address Options</div>
	    </div>
	    <div class="tableRow">
		    <div class="tableCell">
			    <input type="checkbox" name="default_shipping" value="1" <?= $isDefaultShipping ? 'checked' : '' ?> /> Default Shipping <i>(overrides any existing)</i><br/>
			    <input type="checkbox" name="default_billing" value="1" <?= $isDefaultBilling ? 'checked' : '' ?> /> Default Billing <i>(overrides any existing)</i>
		    </div>
	    </div>
    </div>
    <?php	} ?>
    
    <?php if (!$locationReadOnly) { ?>
    <div class="tableBody">
	    <div class="tableRow">
		    <div class="tableCell">
			    <input type="submit" name="btn_submit" class="button" value="Save" />
		    </div>
	    </div>
    </div>
    <?php } ?>
</form>
