<span class="title"><?=$domain_name?></span>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<form name="domain" method="post" action="/_company/domain">
<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
<input type="hidden" name="id" value="<?=$domain->id?>">
<div class="label">Name</div>
<input type="text" name="domain_name" class="value input" value="<?=$domain->name?>"/>
<div class="label">Registrar</div>
<input type="text" name="domain_registrar" class="value input" value="<?=$domain->registrar?>"/>
<div class="label">Registered</div>
<input type="text" name="date_registered" class="value input" value="<?=$domain->date_registered?>"/>
<div class="label">Expires</div>
<input type="text" name="date_expires" class="value input" value="<?=$domain->date_expires?>"/>
<div class="label">Company</div>
<select name="company_id" class="value input">
<?php	foreach ($companies as $company) { ?>
	<option value="<?=$company->id?>"<?php if ($company->id == $domain->company->id) print " selected";?>><?=$company->name?></option>
<?php	} ?>
</select>
<div class="label">Location</div>
<select name="location_id" class="value input">
<?php	foreach ($locations as $location) { ?>
	<option value="<?=$location->id?>"<?php if ($location->id == $domain->location()->id) print " selected";?>><?=$location->name?></option>
<?php	} ?>
</select>
<hr>
<input type="submit" name="btn_submit" value="Submit" class="button"/>
</form>
