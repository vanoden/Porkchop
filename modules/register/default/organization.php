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
    <div class="tableBody min-tablet marginTop_20">
	    <div class="tableRowHeader">
		    <div class="tableCell">Code</div>
		    <div class="tableCell">Name</div>
		    <div class="tableCell">Status</div>
		    <div class="tableCell">Can Resell</div>
		    <div class="tableCell">Reseller</div>
	    </div> <!-- end row header -->
	    <div class="tableRow">
		    <div class="tableCell"><?=$organization->code?></div>
		    <div class="tableCell"><?=$organization->name?></div>
		    <div class="tableCell"><?=$organization->status?></div>
		    <div class="tableCell"><?=$organization->is_reseller ? "Yes" : "No"?></div>
		    <div class="tableCell"><?=($organization->reseller && isset($organization->reseller->name)) ? $organization->reseller->name : ''?></div>
	    </div>
    </div>
    <div class="tableBody">
	    <div class="tableRowHeader">
		    <div class="tableCell">Require Two-Factor Authentication for All Users</div>
		    <div class="tableCell">Password Expires after # Days (0 Never Expires)</div>
		    <div class="tableCell">Website URL</div>
	    </div> <!-- end row header -->
	    <div class="tableRow">
		    <div class="tableCell">
<?php	if ($can_manage) { ?>
			    <input name="time_based_password" type="checkbox" value="1" <?php if($organization->time_based_password) print " checked"?> />
<?php	} elseif ($organization->time_based_password) { ?>
			    <span class="value">Yes</span>
<?php	} else { ?>
			    <span class="value">No</span>
<?php	} ?>
			</div>
			<div class="tableCell">
<?php	if ($can_manage) { ?>
			    <input name="password_expiration_days" type="number" step="1" min="0" max="365" id="password_expiration_days" style="width: 40px; max-width: 40px" value="<?=$organization->password_expiration_days?>" />
<?php	} else { ?>
			    <span class="value"><?=$organization->password_expiration_days?></span>
<?php	} ?>
		    </div>
			<div class="tableCell">
<?php	if ($can_manage) { ?>
			    <input type="text" id="website_url" name="website_url" style="width: 450px; max-width: 450px;" placeholder="http://" value="<?=$organization->website_url?>"/>
<?php	} else { ?>
			    <span class="value"><?=$organization->website_url?></span>
<?php	} ?>
		    </div>
	    </div>
    </div>	
    <div><input type="submit" name="method" value="Apply" class="button"/></div>
    <!--End first row-->

    <div class="user_accounts_container">
        <h3>Current Users</h3>
        <input type="checkbox" id="showAllUsers" name="showAllUsers" value="showAllUsers" onclick="showHidden()" <?=(isset($_REQUEST['showAllUsers']) && !empty($_REQUEST['showAllUsers'])) ? 'checked' : ''?>> SHOW ALL (Expired/Hidden/Deleted)
        <!--	Start First Row-->
        <div class="tableBody bandedRows">
	        <div class="tableRowHeader">
		        <div class="tableCell">Username</div>
		        <div class="tableCell">First Name</div>
		        <div class="tableCell">Last Name</div>
		        <div class="tableCell">Status</div>
		        <div class="tableCell">Last Active</div>
				<div class="tableCell">Last Password Change</div>
	        </div>
        <?php	foreach ($members as $member) {
			$statistics = new \Register\User\Statistics($member->id);
			if ($can_manage) $sub_url = 'org_account';
			else $sub_url = 'account';
		?>
	        <div class="tableRow member_status_<?=strtolower($member->status)?>">
		        <div class="tableCell">
			        <a href="/_register/<?=$sub_url?>?customer_id=<?=$member->id?>"><?=$member->code?></a>
		        </div>
		        <div class="tableCell">
			        <?=$member->first_name?>
		        </div>
		        <div class="tableCell">
			        <?=$member->last_name?>
		        </div>
		        <div class="tableCell">
			        <?=$member->status?>
		        </div>
		        <div class="tableCell">
			        <?=$member->last_active()?>
		        </div>
				<div class="tableCell">
			        <?=$statistics?->last_password_change_date?->format('Y-m-d H:i:s')?>
				</div>
	        </div>
        <?php	} ?>
        </div>
        <!--End first row-->

        <h3>Automation Users</h3>
        <!--	Start First Row-->
        <?php	if ($organization->id) {
			if ($can_manage) $sub_url = 'org_account';
			else $sub_url = 'account';
		?>
        <div class="tableBody min-tablet">
	        <div class="tableRowHeader">
		        <div class="tableCell value tableCell-width-20">Username</div>
		        <div class="tableCell value tableCell-width-10">Status</div>
		        <div class="tableCell value tableCell-width-30">Last Active</div>
	        </div>
        <?php	foreach ($automationMembers as $member) { ?>
	        <div class="tableRow member_status_<?=strtolower($member->status)?>">
		        <div class="tableCell">
			        <a href="/_register/<?=$sub_url?>?customer_id=<?=$member->id?>"><?=$member->code?></a>
		        </div>
		        <div class="tableCell">
			        <?=$member->status?>
		        </div>
		        <div class="tableCell">
			        <?=$member->last_active()?>
		        </div>
	        </div>
        <?php	} ?>
        </div>
        <!--End first row-->
        <?php	} ?>
    </div>

    <h3>Locations</h3>
    <!--	Start First Row-->
    <div class="tableBody">
	    <div class="tableRowHeader">
        	<div class="tableCell value tableCell-width-5">Default Billing</div>
        	<div class="tableCell value tableCell-width-5">Default Shipping</div>
		    <div class="tableCell value tableCell-width-20">Name</div>
		    <div class="tableCell value tableCell-width-20">Address</div>
		    <div class="tableCell value tableCell-width-20">City</div>
		    <div class="tableCell value tableCell-width-20">Province/Region</div>
	    </div>
	    	    
    <?php	foreach ($locations as $location) {
			if ($can_manage) $disabled = '';
			else $disabled = 'disabled';
		?>
	    <div class="tableRow">
	        <div class="tableCell">
        	    <input type="radio" name="default_billing_location_id" <?php if ($organization->default_billing_location_id == $location->id) echo "checked='checked'"; ?> <?=$disabled?> value="<?=$location->id?>" onclick="submitDefaultLocation('setDefaultBilling',<?=$location->id?>)">
	        </div>
	        <div class="tableCell">	    	
        	    <input type="radio" name="default_shipping_location_id" <?php if ($organization->default_shipping_location_id == $location->id) echo "checked='checked'"; ?> <?=$disabled?> value="<?=$location->id?>" onclick="submitDefaultLocation('setDefaultShipping',<?=$location->id?>)">
	        </div>
		    <div class="tableCell">
			    <a href="/_register/location?organization_id=<?=$organization->id?>&id=<?=$location->id?>"><?=$location->name?></a>
		    </div>
		    <div class="tableCell">
			    <?=$location->address_1?>
		    </div>
		    <div class="tableCell">
			    <?=$location->city?>
		    </div>
		    <div class="tableCell">
			    <?=$location->province()->name?><br/>
			    <?=$location->province()->country()->name?>
		    </div>
	    </div>
    <?php	} ?>
    </div>
    <div><input type="button" name="method" value="Add Location" class="button" onclick="addLocation()"/></div>
    <!--End first row-->
</form>
</div>
</section>
