<script language="JavaScript">
	function addLocation() {
		var organization_id = document.forms[0].organization_id.value;
		window.location.href = "/_register/admin_location?organization_id="+organization_id;
		return true;
	}
	
	function submitDefaultLocation(type, value) {
		var organization_id = document.forms[0].organization_id.value;
		window.location.href = "/_register/admin_organization_locations/<?=$organization->code?>?organization_id="+organization_id+"&" + type + "=" + value;
		return true;
	}
</script>

<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<?php $activeTab = 'locations'; ?>
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

<form id="orgLocations" name="orgLocations" method="POST">
    <input type="hidden" name="organization_id" value="<?=$organization->id?>"/>
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    
    <div class="form_instruction">Manage locations for this organization. Existing addresses are view-only; use Copy to create a new address with changes, or Hide/Unhide for addresses no longer used.</div>

    <h3>Locations</h3>
    <p>
    <form method="get" action="/_register/admin_organization_locations/<?= htmlspecialchars($organization->code) ?>" class="inline-form" style="display:inline;">
      <input type="hidden" name="organization_id" value="<?= (int)$organization->id ?>" />
      <label><input type="checkbox" name="show_hidden" value="1" <?= !empty($show_hidden) ? 'checked' : '' ?> onchange="this.form.submit()" /> Show hidden addresses</label>
    </form>
    </p>
    <!--	Start First Row-->
    <div class="tableBody">
	    <div class="tableRowHeader">
        	<div class="tableCell value width-5per">Default Billing</div>
        	<div class="tableCell value width-5per">Default Shipping</div>
		    <div class="tableCell value width-15per">Name</div>
		    <div class="tableCell value width-15per">Address</div>
		    <div class="tableCell value width-10per">City</div>
		    <div class="tableCell value width-15per">Province/Region</div>
		    <div class="tableCell value width-5per">Hidden</div>
		    <div class="tableCell value width-15per">Actions</div>
	    </div>
	    	    
    <?php	foreach ($locations as $location) {
        $isHidden = !empty($location->hidden);
    ?>
	    <div class="tableRow"<?= $isHidden ? ' style="color: #999;"' : '' ?>>
	        <div class="tableCell">
        	    <input type="radio" name="default_billing_location_id" <?php if ($organization->default_billing_location_id == $location->id) echo "checked='checked'"; ?> value="<?=$location->id?>" onclick="submitDefaultLocation('setDefaultBilling',<?=$location->id?>)">
	        </div>
	        <div class="tableCell">	    	
        	    <input type="radio" name="default_shipping_location_id" <?php if ($organization->default_shipping_location_id == $location->id) echo "checked='checked'"; ?> value="<?=$location->id?>" onclick="submitDefaultLocation('setDefaultShipping',<?=$location->id?>)">
	        </div>
		    <div class="tableCell">
			    <a href="/_register/admin_location?organization_id=<?=$organization->id?>&id=<?=$location->id?>"><?= htmlspecialchars($location->name) ?></a>
		    </div>
		    <div class="tableCell">
			    <?= htmlspecialchars($location->address_1) ?>
		    </div>
		    <div class="tableCell">
			    <?= htmlspecialchars($location->city) ?>
		    </div>
		    <div class="tableCell">
			    <?= htmlspecialchars($location->province()->name) ?><br/>
			    <?= htmlspecialchars($location->province()->country()->name) ?>
		    </div>
		    <div class="tableCell">
			    <?= $isHidden ? 'Yes' : 'No' ?>
		    </div>
		    <div class="tableCell">
			    <a href="/_register/admin_location?organization_id=<?=$organization->id?>&amp;copy_id=<?=$location->id?>">Copy</a>
			    <?php if ($isHidden) { ?>
			    | <a href="/_register/admin_organization_locations/<?= $organization->code ?>?organization_id=<?=$organization->id?>&amp;setVisible=<?=$location->id?>">Unhide</a>
			    <?php } else { ?>
			    | <a href="/_register/admin_organization_locations/<?= $organization->code ?>?organization_id=<?=$organization->id?>&amp;setHidden=<?=$location->id?>">Hide</a>
			    <?php } ?>
		    </div>
	    </div>
    <?php	} ?>
    </div>
    <div><input type="button" name="method" value="Add Location" class="button" onclick="addLocation()"/></div>
    <!--End first row-->
</form>
