<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->
<div class="tableBody">
  <div class="tableRowHeader">
    <div class="tableCell">Code</div>
    <div class="tableCell">Name</div>
    <div class="tableCell">Host</div>
    <div class="tableCell">Domain</div>
  </div>
  <?php	foreach ($locations as $location) { ?>
  <div class="tableRow">
    <div class="tableCell"><a href="/_company/location?id=<?=$location->id?>"><?=$location->code?></a></div>
    <div class="tableCell"><?=$location->name?></div>
    <div class="tableCell"><?=$location->host?></div>
    <div class="tableCell"><?=$location->domain()->name?></div>
  </div>
<?php } ?>
</div>

<!-- entire page button bar -->
<div id="submit-button-container" class="tableBody min-tablet">
  <div class="tableRow button-bar">
    <a href="/_company/location" class="input button" id="btn_submit">Add a location</a>
  </div>
</div>