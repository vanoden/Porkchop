<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->
<form name="location" method="post" action="/_company/location">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<input type="hidden" name="id" value="<?=isset($location) && $location ? $location->id : ''?>">
	<div class="label">Code</div>
	<span class="value input"><?=isset($location) && $location ? ($location->code ?? '') : ''?></span>
	<div class="label">Name</div>
	<input type="text" name="name" class="value input" value="<?=isset($location) && $location ? htmlspecialchars($location->name ?? '') : ''?>"/>
	<div class="label">Address Line 1</div>
	<input type="text" name="address_1" class="value input" value="<?=isset($location) && $location ? htmlspecialchars($location->address_1 ?? '') : ''?>"/>
	<div class="label">Address Line 2</div>
	<input type="text" name="address_2" class="value input" value="<?=isset($location) && $location ? htmlspecialchars($location->address_2 ?? '') : ''?>"/>
	<div class="label">City</div>
	<input type="text" name="city" class="value input" value="<?=isset($location) && $location ? htmlspecialchars($location->city ?? '') : ''?>"/>
	<div class="label">Region</div>
	<select name="state_id">
<?php	foreach ($states as $state) { ?>
		<option value="<?=$state->id?>"<?php if (isset($location) && $location && $state->id == ($location->state_id ?? null)) print " selected";?>><?=$state->name?></option>
<?php	} ?>
	</select>
	<div class="label">Company</div>
	<select name="company_id" class="value input">
<?php	foreach ($companies as $company) { ?>
		<option value="<?=$company->id?>"<?php if (isset($location) && $location && $company->id == ($location->company_id ?? null)) print " selected";?>><?=$company->name?></option>
<?php	} ?>
	</select>
	<div class="label">Domain</div>
	<select name="domain_id" class="value input">
<?php	foreach ($domains as $domain) { ?>
		<option value="<?=$domain->id?>"<?php if (isset($location) && $location && $domain->id == ($location->domain_id ?? null)) print " selected";?>><?=$domain->name?></option>
<?php	} ?>
	</select>
	<input type="submit" name="btn_submit" value="Submit" class="button"/>
</form>