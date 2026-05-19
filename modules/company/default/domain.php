<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->
<form name="domain" method="post" action="/_company/domain" class="section-grid grid-col-4">
	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
	<input type="hidden" name="id" value="<?=$domain->id?>">
	<div class="form-field">
    <label for="domain_name">Name</label>
    <input type="text" id="domain_name" name="domain_name" class="value input" value="<?=$domain->name?>"/>
  </div>

  <div class="form-field">
    <label for="domain_registrar">Registrar</label>
    <input type="text" id="domain_registrar" name="domain_registrar" class="value input" value="<?=$domain->registrar?>"/>
  </div>

  <div class="form-field">
    <label for="date_registered">Registered</label>
    <input type="date" id="date_registered" name="date_registered" class="value input" value="<?=$domain->date_registered?>"/>
  </div>

  <div class="form-field">
    <label for="date_expires">Expires</label>
    <input type="date" id="date_expires" name="date_expires" class="value input" value="<?=$domain->date_expires?>"/>
  </div>

  <div class="form-field">
    <label for="company_id">Company</label>
    <select id="company_id" name="company_id" class="value input" placeholder="GoDaddy.com, LLC">
      <?php foreach ($companies as $company) { ?>
          <option value="<?=$company->id?>"<?php if ($company->id == $domain->company()->id) print " selected";?>><?=$company->name?></option>
      <?php } ?>
    </select>
  </div>

  <div class="form-field">
    <label for="location_id">Location</label>
    <select id="location_id" name="location_id" class="value input">
      <?php foreach ($locations as $location) { ?>
          <option value="<?=$location->id?>"<?php if ($location->id == $domain->location()->id) print " selected";?>><?=$location->name?></option>
      <?php } ?>
    </select>
  </div>
	<button type="submit" name="btn_submit" value="Submit">Submit</button>
</form>
