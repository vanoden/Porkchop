<script type="text/javascript" src="/js/geography.js"></script>
<script language="Javascript">
	function popProvinceSelect() {
		var countryID = document.forms[0].country_id.value;
		console.log("Getting country "+countryID);
		var country = Object.create(Country);
		country.id = countryID;
		var provinces = country.getProvinces();
		//console.log(provinces);

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
<div class="title">Location</div>
<?php	if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?php	}
	if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?php	} ?>
<form name="locationForm" method="post" action="/_register/admin_location">
<input type="hidden" name="id" value="<?=$location->id?>" />
<?php	if (isset($_REQUEST['organization_id'])) { ?>
<div class="container">
	<span class="label">Organization</span>
	<a href="/_register/organization/<?=$organization->code?>" class="value"><?=$organization->name?></a>
	<input type="hidden" name="organization_id" value="<?=$organization->id?>" />
</div>
<?php	}
	if (isset($_REQUEST['user_id'])) { ?>
<div class="container">
	<span class="label">Customer</span>
	<span class="value"><?=$user->full_name()?></span>
	<input type="hidden" name="user_id" value="<?=$user->id?>" />
</div>
<?php	} ?>
<div class="container">
	<span class="label">Name</span>
	<input type="text" name="name" class="value input" value="<?=$location->name?>" />
</div>
<div class="container">
	<span class="label">Address 1</span>
	<input type="text" name="address_1" class="value input" value="<?=$location->address_1?>" />
</div>
<div class="container">
	<span class="label">Address 2</span>
	<input type="text" name="address_2" class="value input" value="<?=$location->address_2?>" />
</div>
<div class="container">
	<span class="label">City</span>
	<input type="text" name="city" class="value input" value="<?=$location->city?>" />
</div>
<div class="container">
	<span class="label">Country</span>
	<select name="country_id" class="value input" onchange="popProvinceSelect();">
		<option value="">Select</option>
<?php	foreach ($countries as $country) { ?>
		<option value="<?=$country->id?>"<?php	if ($country->id == $selected_country->id) print " selected"; ?>><?=$country->name?></option>
<?php	} ?>
	</select>
</div>
<div class="container">
	<span class="label">State/Province</span>
	<select name="province_id" class="value input">
<?php	if (isset($country->id) && $country->id > 0 && is_array($provinces)) {
			foreach ($provinces as $province) {
?>		<option value="<?=$province->id?>"<?php	if ($province->id == $selected_province->id) print " selected"; ?>><?=$province->name?></option>
<?php			}
		} else { ?>
		<option value="">Choose Country First</option>
<?php		} ?>
	</select>
</div>
<div class="container">
	<span class="label">Zip Code</span>
	<input type="text" name="zip_code" class="value input" value="<?=$location->zip_code?>" />
</div>
<div class="form_footer">
	<input type="submit" name="btn_submit" class="button" value="Save" />
</div>
</form>
