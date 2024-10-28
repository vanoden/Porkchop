<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->
<div class="tableBody">
  <div class="tableRowHeader">
    <div class="tableCell">Name</div>
    <div class="tableCell">Created</div>
    <div class="tableCell">Registered</div>
    <div class="tableCell">Expires</div>
    <div class="tableCell">Company</div>
	<div class="tableCell">Location</div>
  </div>
  <?php	foreach ($domains as $domain) { ?>
  <div class="tableRow">
    <div class="tableCell"><a href="/_company/domain?name=<?=$domain->name()?>"><?=$domain->name()?></a></div>
    <div class="tableCell"><?=$domain->date_created?></div>
    <div class="tableCell"><?=$domain->date_registered?></div>
    <div class="tableCell"><?=$domain->date_expires?></div>
    <div class="tableCell"><?=$domain->company()->name?></div>
	<div class="tableCell"><?=$domain->location()->name?></div>
  </div>
<?php } ?>
</div>

<!-- entire page button bar -->
<div id="submit-button-container" class="tableBody min-tablet">
  <div class="tableRow button-bar">
    <a href="/_company/domain" class="input button" id="btn_submit">Add a domain</a>
  </div>
</div>