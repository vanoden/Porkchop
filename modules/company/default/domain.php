<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<div class="form_instruction">
	<strong>Domain Management:</strong> Configure domain settings including registration details, expiration dates, and associated company/location information.
</div>

<form name="domain" method="post" action="/_company/domain">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<input type="hidden" name="id" value="<?=isset($domain) && $domain ? $domain->id : ''?>">

	<h3>Domain Information</h3>
	<section class="tableBody clean min-tablet">
		<div class="tableRowHeader">
			<div class="tableCell width-25per">Field</div>
			<div class="tableCell width-75per">Value</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<span class="label">Domain Name</span>
			</div>
			<div class="tableCell">
				<input type="text" name="domain_name" class="value input width-100per" value="<?=isset($domain) && $domain ? htmlspecialchars($domain->name ?? '') : ''?>" placeholder="Enter domain name (e.g., example.com)" required />
			</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<span class="label">Registrar</span>
			</div>
			<div class="tableCell">
				<input type="text" name="domain_registrar" class="value input width-100per" value="<?=isset($domain) && $domain ? htmlspecialchars($domain->registrar ?? '') : ''?>" placeholder="Enter domain registrar (e.g., GoDaddy, Namecheap)" />
			</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<span class="label">Registration Date</span>
			</div>
			<div class="tableCell">
				<input type="date" name="date_registered" class="value input width-100per" value="<?=isset($domain) && $domain ? $domain->date_registered : ''?>" />
			</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<span class="label">Expiration Date</span>
			</div>
			<div class="tableCell">
				<input type="date" name="date_expires" class="value input width-100per" value="<?=isset($domain) && $domain ? $domain->date_expires : ''?>" />
			</div>
		</div>
	</section>

	<h3>Organization Assignment</h3>
	<section class="tableBody clean min-tablet">
		<div class="tableRowHeader">
			<div class="tableCell width-25per">Field</div>
			<div class="tableCell width-75per">Value</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<span class="label">Company</span>
			</div>
			<div class="tableCell">
				<select name="company_id" class="value input width-100per" required>
					<option value="">Select Company</option>
<?php	foreach ($companies as $company) { ?>
					<option value="<?=$company->id?>"<?php if (isset($domain) && $domain && $company->id == ($domain->company_id ?? null)) print " selected";?>><?=htmlspecialchars($company->name)?></option>
<?php	} ?>
				</select>
			</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<span class="label">Location</span>
			</div>
			<div class="tableCell">
				<select name="location_id" class="value input width-100per">
					<option value="">Select Location (Optional)</option>
<?php	foreach ($locations as $location) { ?>
					<option value="<?=$location->id?>"<?php if (isset($domain) && $domain && $location->id == ($domain->location_id ?? null)) print " selected";?>><?=htmlspecialchars($location->name)?></option>
<?php	} ?>
				</select>
			</div>
		</div>
	</section>

	<div id="submit-button-container" class="tableBody min-tablet">
		<div class="tableRow button-bar">
	    <input type="submit" name="btn_submit" value="Submit" class="button"/>
		</div>
	</div>
</form>
