<style>
    
    .user_accounts_container {
        margin-top: 50px; 
        border: solid 1px #9a9a9a; 
        padding:10px 10px 50px 10px;
    }
    
    .member_status_expired, .member_status_hidden, .member_status_deleted, .member_status_blocked .tableCell{
        color:#999;
        font-style:italic;
    }
</style>
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

<form id="orgDetails" name="orgDetails" method="POST">

    <input type="hidden" name="organization_id" value="<?=$organization->id?>"/>
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <input type="hidden" id="removeTagId" name="removeTagId" value=""/>
    
    <div class="form_instruction">Make changes and click 'Apply' to complete.</div>

    <!--	Start First Row-->
    <div class="tableBody min-tablet marginTop_20">
	    <div class="tableRowHeader">
		    <div class="tableCell" style="width: 20%;">Code</div>
		    <div class="tableCell" style="width: 20%;">Name</div>
		    <div class="tableCell" style="width: 15%;">Status</div>
		    <div class="tableCell" style="width: 10%;">Can Resell</div>
		    <div class="tableCell" style="width: 15%;">Reseller</div>
		    <div class="tableCell" style="width: 15%;">Password Exp. (days)</div>
	    </div> <!-- end row header -->
	    <div class="tableRow">
		    <div class="tableCell">
			    <input name="code" type="text" id="organizationCodeValue" class="wide_100per" value="<?=$organization->code?>" />
		    </div>
		    <div class="tableCell">
			    <input name="name" type="text" id="organizationNameValue" class="wide_100per" value="<?=$organization->name?>" />
		    </div>
		    <div class="tableCell">
			    <select name="status" id="organizationStatusValue" class="wide_100per">
				    <?php		foreach ($statii as $status) { ?>
				    <option value="<?=$status?>"<?php	if ($status == $organization->status) print " selected"; ?>><?=$status?></option>
				    <?php		} ?>
			    </select>
		    </div>
		    <div class="tableCell">
			    <input name="is_reseller" type="checkbox" value="1" <?php	if($organization->is_reseller) print " checked"?> />
		    </div>
		    <div class="tableCell">
			    <select name="assigned_reseller_id" class="wide_100per">
				    <option value="">Select</option>
				    <?php	
				    foreach ($resellers as $reseller) {
				        if ($organization->id == $reseller->id) continue;
				    ?>
				        <option value="<?=$reseller->id?>"<?php	if($organization->reseller->id == $reseller->id) print " selected";?>><?=$reseller->name?></option>
				    <?php
				    } 
				    ?>
			    </select>
		    </div>
		    <div class="tableCell">
			    <input name="password_expiration_days" type="number" step="1" min="0" max="365" id="password_expiration_days" class="wide_100per" value="<?=$organization->password_expiration_days?>" />
		    </div>
	    </div>
    </div>
    <div class="tableBody">
	    <div class="tableRowHeader">
		    <div class="tableCell" style="width: 100%;">Require Two-Factor Authentication</div>
	    </div> <!-- end row header -->
	    <div class="tableRow">
		    <div class="tableCell">
			    <input name="time_based_password" type="checkbox" value="1" <?php if($organization->time_based_password) print " checked"?> />
			    <label for="time_based_password">Require two-factor authentication for all users in this organization</label>
		    </div>
	    </div>
    </div>
    <div class="tableBody">
	    <div class="tableRowHeader">
		    <div class="tableCell" style="width: 100%;">Notes</div>
	    </div> <!-- end row header -->
	    <div class="tableRow">
		    <div class="tableCell">
			    <textarea name="notes" class="wide_lg"><?=strip_tags($organization->notes)?></textarea>
		    </div>
	    </div>
    </div>
    <div class="tableBody">
	    <div class="tableRowHeader">
		    <div class="tableCell" style="width: 100%;">Website URL</div>
	    </div> <!-- end row header -->
	    <div class="tableRow">
		    <div class="tableCell">
			    <input id="website_url" name="website_url" class="wide_lg" placeholder="http://" value="<?=$organization->website_url?>"/>
		    </div>
	    </div>
    </div>	
    <div><input type="submit" name="method" value="Apply" class="button"/></div>
	<input type="button" name="btnAuditLog" value="Audit Log" onclick="location.href='/_register/organization_audit_log?organization_id=<?=$organization->id?>';" />

    <!--End first row-->
	<?php	if ($organization->id) { ?>
    <h3>Add Organization Tag</h3>
    <div class="tableBody min-tablet">
	    <div class="tableRowHeader">
		    <div class="tableCell" style="width: 35%;">Tag</div>
	    </div>
        <?php	
            foreach ($organizationTags as $tag) {
        ?>
	        <div class="tableRow">
		        <div class="tableCell">
			        <input type="button" onclick="removeTagById('<?=$tag->id?>')" name="removeTag" value="Remove" class="button"/> <strong><?=$tag->name?></strong>
		        </div>
	        </div>
        <?php	
            } 
        ?>
	    
	    <div class="tableRow">
		    <div class="tableCell"><label>New Tag</label><input type="text" class="" name="newTag" value="" /></div>
	    </div>
    </div>
    <div><input type="submit" name="addTag" value="Add Tag" class="button"/></div>
    <div class="user_accounts_container">
        <input type="checkbox" id="showAllUsers" name="showAllUsers" value="showAllUsers" onclick="showHidden()" <?=(isset($_REQUEST['showAllUsers']) && !empty($_REQUEST['showAllUsers'])) ? 'checked' : ''?>> SHOW ALL (Expired/Hidden/Deleted)
        <h3>Current Users</h3>
        <!--	Start First Row-->
        <div class="tableBody">
	        <div class="tableRowHeader">
		        <div class="tableCell value" style="width: 20%;">Username</div>
		        <div class="tableCell value" style="width: 20%;">First Name</div>
		        <div class="tableCell value" style="width: 20%;">Last Name</div>
		        <div class="tableCell value" style="width: 10%;">Status</div>
		        <div class="tableCell value" style="width: 30%;">Last Active</div>
	        </div>
        <?php	foreach ($members as $member) { ?>
	        <div class="tableRow member_status_<?=strtolower($member->status)?>">
		        <div class="tableCell">
			        <a href="/_register/admin_account?customer_id=<?=$member->id?>"><?=$member->code?></a>
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
	        </div>
        <?php	} ?>
        </div>
        <!--End first row-->

        <h3>Automation Users</h3>
        <!--	Start First Row-->
        <?php	if ($organization->id) { ?>
        <div class="tableBody min-tablet">
	        <div class="tableRowHeader">
		        <div class="tableCell value" style="width: 20%;">Username</div>
		        <div class="tableCell value" style="width: 10%;">Status</div>
		        <div class="tableCell value" style="width: 30%;">Last Active</div>
	        </div>
        <?php	foreach ($automationMembers as $member) { ?>
	        <div class="tableRow member_status_<?=strtolower($member->status)?>">
		        <div class="tableCell">
			        <a href="/_register/admin_account?customer_id=<?=$member->id?>"><?=$member->code?></a>
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
		    
    <h3>Add New User</h3>
    <!--	Start First Row-->
    <div class="tableBody">
	    <div class="tableRowHeader">
		    <div class="tableCell" style="width: 35%;">Username</div>
		    <div class="tableCell" style="width: 30%;">First Name</div>
		    <div class="tableCell" style="width: 35%;">Last Name</div>
	    </div>
	    <div class="tableRow">
		    <div class="tableCell"><input type="text" class="wide_100per" name="new_login" value="" /></div>
		    <div class="tableCell"><input type="text" class="wide_100per" name="new_first_name" value="" /></div>
		    <div class="tableCell"><input type="text" class="wide_100per" name="new_last_name" value="" /></div>
	    </div>
    </div>
    <div><input type="submit" name="method" value="Add User" class="button"/></div>

    <h3>Locations</h3>
    <!--	Start First Row-->
    <div class="tableBody">
	    <div class="tableRowHeader">
        	<div class="tableCell value" style="width: 5%;">Default Billing</div>
        	<div class="tableCell value" style="width: 5%;">Default Shipping</div>
		    <div class="tableCell value" style="width: 20%;">Name</div>
		    <div class="tableCell value" style="width: 20%;">Address</div>
		    <div class="tableCell value" style="width: 20%;">City</div>
		    <div class="tableCell value" style="width: 20%;">Province/Region</div>
	    </div>
	    	    
    <?php	foreach ($locations as $location) { ?>
	    <div class="tableRow">
	        <div class="tableCell">
        	    <input type="radio" name="default_billing_location_id" <?php if ($organization->default_billing_location_id == $location->id) echo "checked='checked'"; ?> value="<?=$location->id?>" onclick="submitDefaultLocation('setDefaultBilling',<?=$location->id?>)">
	        </div>
	        <div class="tableCell">	    	
        	    <input type="radio" name="default_shipping_location_id" <?php if ($organization->default_shipping_location_id == $location->id) echo "checked='checked'"; ?> value="<?=$location->id?>" onclick="submitDefaultLocation('setDefaultShipping',<?=$location->id?>)">
	        </div>
		    <div class="tableCell">
			    <a href="/_register/admin_location?organization_id=<?=$organization->id?>&id=<?=$location->id?>"><?=$location->name?></a>
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
    <?php	} ?>
</form>
