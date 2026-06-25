<script type="text/javascript">
	function submitForm() {
		return true;
	}
	function submitSearch(start) {
		document.getElementById('start').value=start;
		document.getElementById('orgSearch').submit();
		return true;
	}
</script>


<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<form id="orgSearch" method="get">
<div id="search_container" class="section-flex cluster--wide">
	<div class="form-field">
    <label for="searchOrganizationInput">Organization Name</label>
    <input type="text" id="searchOrganizationInput" name="name" placeholder="organization name" value="<?php if (!empty($_REQUEST["name"])) print $_REQUEST["name"];?>"/>
  </div>
  <!-- Check boxes -->
  <div class="section-flex cluster--tight">
    <div class="section-flex cluster">
      <input type="checkbox" name="hidden" class="checkbox" value="1" <?php if (!empty($_REQUEST['hidden'])) print "checked"; ?> />
      <label>Hidden</label>
    </div>
    <div class="section-flex cluster">
      <input type="checkbox" name="expired" class="checkbox" value="1" <?php if (!empty($_REQUEST['expired'])) print "checked"; ?> />
      <label>Expired</label>
    </div>
    <div class="section-flex cluster">
      <input type="checkbox" name="deleted" class="checkbox" value="1" <?php if (!empty($_REQUEST['deleted'])) print "checked"; ?> />
      <label>Deleted</label>
    </div>
  </div>

	<input type="hidden" id="start" name="start" value="0">

	<div class="form-field">
		<label>Filter by Tag:</label>
		<select name="searchedTag" id="organizationStatusValue" class="">
			<option value="">Select Tag</option>
		<?php	foreach ($organizationTags as $tag) { print_r($tag); ?>
			<option value="<?=$tag?>"<?php	if ($tag == $_REQUEST['searchedTag']) print " selected"; ?>><?=$tag?></option>
		<?php	} ?>
		</select>
	</div>

	<div class="form-field">
    <label>Records per page:</label><input type="text" name="<?=$pagination->sizeElemName?>" class="value input register-organizations-pagination-size" value="<?=$pagination->size()?>" />
  </div>

  <button id="searchOrganizationButton" name="btn_search" onclick="submitSearch(0)">Search</button>
</div>



<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">ID</div>
		<div class="tableCell">Name</div>
		<div class="tableCell">Status</div>
		<div class="tableCell">Members</div>
		<div class="tableCell">Devices</div>
	</div>
	<?php
		foreach ($organizations as $organization) {
	  ?>
	<div class="tableRow">
		<div class="tableCell"><a href="<?=PATH."/_register/organization?organization_id=".$organization->id?>"><?=$organization->code?></a></div>
		<div class="tableCell"><?=$organization->name?></div>
		<div class="tableCell"><?=$organization->status?></div>
		<div class="tableCell"><?=$organization->activeHumans()?></div>
		<div class="tableCell"><?=$organization->activeDevices()?></div>
	</div>
	<?php
		}
		if (!is_array($organizations) || !count($organizations)) {
	?>
	<div class="tableRow">
		<div class="tableCell"><p>No Organizations Found</p></div>
	</div>
	<?php
		}
	?>
</div><!-- end table -->

<!--    Standard Page Navigation Bar -->
<?=$pagination->renderBar()?>
</form>

<form action="<?=PATH?>/_register/admin_organization" method="get">
	<div class="form-actions filter-bar">
		<div class="button-group filter-bar__actions">
			<button type="submit" name="button_submit" value="Add Organization">Add Organization</button>
		</div>
	</div>
</form>