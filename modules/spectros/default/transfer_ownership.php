<script language="Javascript" src="/js/monitor.js"></script>
<script language="Javascript" src="/js/register.js"></script>
<script language="Javascript">
	function selectedDevice() {
		var index = document.getElementById('asset_id').selectedIndex;
		var code = document.getElementById('asset_id').options[index].text;
		var asset = Object.create(Asset);
		asset.get(code);
		document.getElementById('transferDeviceProduct').innerHTML = asset.product.code;
		document.getElementById('transferDeviceDescription').innerHTML = asset.product.description;
		document.getElementById('transferDeviceOwner').innerHTML = asset.organization.name;
		document.getElementById('transferDeviceDetails').style.display = 'block';
		
		var account = Object.create(Customer);
		account.get(code);
		if (typeof(account.code) != "undefined" && account.code.length > 0) {
			document.getElementById('transferDeviceAccount').innerHTML = account.code;
			document.getElementById('transferDeviceOrganization').innerHTML = account.organization.name;
			document.getElementById('transferDeviceAccountConfirm').disabled = false;
			document.getElementById('transferDeviceAccountConfirm').checked = true;
			document.getElementById('transferAccountDetails').style.display = 'block';
		}
		else {
			document.getElementById('transferDeviceAccount').innerHTML = 'None';
			document.getElementById('transferDeviceOrganization').innerHTML = 'None';
			document.getElementById('transferDeviceAccountConfirm').disabled = true;
			document.getElementById('transferDeviceAccountConfirm').checked = false;
		}
		console.log(account);
	}
</script>
<div class="title">Transfer Device Ownership</div>
<?php	 if ($page->errorCount() > 0) { ?>
    <div class="form_error"><?=$page->errorString()?></div>
<?php	 } ?>
<?php
	if ($page->success) {
?>
<div class="form_success"><?=$page->success?></div>
<?php	}
	else {
?>
<div class="form_instruction">Please Select a Device to Transfer, an Organization to Transfer it to and a reason for the transfer.</div>
<?php	} ?>
<form id="transferForm" method="post">
<div class="question_container">
	<div class="label">Device Serial</div>
	<select id="asset_id" class="input" name="asset_id" onchange="selectedDevice();">
		<option value="">Select One</option>
<?php	foreach ($assets as $asset) { ?>
		<option value="<?=$asset->id?>" <?php if ($_REQUEST['asset_id'] == $asset->id) print "selected";?>><?=$asset->code?></option>
<?php	} ?>
	</select>
</div>
<div class="transferDeviceDetailContainer" id="transferDeviceDetails">
	<span class="label transferDeviceLabel">Device Model</span>
	<span class="value transferDeviceValue" id="transferDeviceProduct"></span>
	<span class="label transferDeviceLabel">Description</span>
	<span class="value transferDeviceValue" id="transferDeviceDescription"></span>
	<span class="label transferDeviceLabel">Associated Account</span><span id="transferDeviceOwner"></span>
</div>
<div class="transferDeviceDetailContainer" id="transferAccountDetails">
	<span class="label transferDeviceLabel">Associated Account</span>
	<span class="value transferDeviceValue" id="transferDeviceAccount"></span>
	<span class="label transferDeviceLabel">Account Organization</span>
	<span class="value transferDeviceValue" id="transferDeviceOrganization"></span>
	<span class="label transferDeviceLabel">Transfer Account?</span>
	<input type="checkbox" value="1" id="transferDeviceAccountConfirm" name="transferDeviceAccountConfirm" />
</div>
<div class="question_container">
	<div class="label">Transfer To</div>
	<select class="input" name="organization_id">
		<option value="">Select One</option>
<?php	foreach ($organizations as $organization) { ?>
		<option value="<?=$organization->id?>" <?php if ($organization->id == $_REQUEST['organization_id']) print "selected";?>><?=$organization->name?></option>
<?php	} ?>
	</select>
</div>
<div class="question_container">
	<div class="label">Reason</div>
	<select class="input" name="reason">
		<option value="">Select One</option>
		<option value="sold">Sold</option>
		<option value="correction">Correction</option>
	</select>
</div>
<div class="form_footer"><input type="submit" class="button" name="btn_submit" value="Submit" /></div>
