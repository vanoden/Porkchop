<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->
<form name="domain" method="post" action="/_company/domain" class="form-admin-edit">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<input type="hidden" name="id" value="<?=$domain->id?>">

	<div class="filter-bar">
		<div class="filter-bar__controls">
			<div class="form-field filter-bar__search">
				<label for="domain_name">Name</label>
				<input type="text" id="domain_name" name="domain_name" value="<?=$domain->name?>"/>
			</div>

			<div class="form-field">
				<label for="domain_registrar">Registrar</label>
				<input type="text" id="domain_registrar" name="domain_registrar" value="<?=$domain->registrar?>"/>
			</div>

			<div class="form-field">
				<label for="date_registered">Registered</label>
				<input type="date" id="date_registered" name="date_registered" value="<?=$domain->date_registered?>"/>
			</div>

			<div class="form-field">
				<label for="date_expires">Expires</label>
				<input type="date" id="date_expires" name="date_expires" value="<?=$domain->date_expires?>"/>
			</div>

			<div class="form-field">
				<label for="company_id">Company</label>
				<select id="company_id" name="company_id">
					<?php foreach ($companies as $company) { ?>
						<option value="<?=$company->id?>"<?php if ($company->id == $domain->company()->id) print " selected";?>><?=$company->name?></option>
					<?php } ?>
				</select>
			</div>

			<div class="form-field">
				<label for="location_id">Location</label>
				<select id="location_id" name="location_id">
					<?php foreach ($locations as $location) { ?>
						<option value="<?=$location->id?>"<?php if ($location->id == $domain->location()->id) print " selected";?>><?=$location->name?></option>
					<?php } ?>
				</select>
			</div>
		</div>

		<div class="button-group filter-bar__actions">
			<button type="submit" name="btn_submit" class="button" value="Submit">Submit</button>
		</div>
	</div>
</form>
