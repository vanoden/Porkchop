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
<style>
	.label { text-align: left; }
	th.organizationsCodeLabel { width: 150px; }
	th.organizationsNameLabel { width: 540px; }
	td.value { overflow: hidden; }
	a.pager { margin: 5px; }
</style>

<!-- Page Header -->
<?=$page->showTitle()?>
<?=$page->showBreadcrumbs()?>
<?=$page->showMessages()?>
<!-- End Page Header -->

<form id="orgSearch" method="get" class="float: left">
  <div id="search_container">
    <div><input type="text" id="searchOrganizationInput" name="name" placeholder="organization name" value="<?php if (!empty($_REQUEST["name"])) print $_REQUEST["name"];?>"/></div>
    <div><input type="checkbox" name="hidden" class="checkbox" value="1" <?php if (!empty($_REQUEST['hidden'])) print "checked"; ?> /><label>Hidden</label></div>
    <div><input type="checkbox" name="expired" class="checkbox" value="1" <?php if (!empty($_REQUEST['expired'])) print "checked"; ?> /><label>Expired</label></div>
    <div><input type="checkbox" name="deleted" class="checkbox" value="1" <?php if (!empty($_REQUEST['deleted'])) print "checked"; ?> /><label>Deleted</label></div>
    <div><label></label><input type="hidden" id="start" name="start" value="0"></div>
    <div>
      <label>Filter by Tag:</label>
      <select name="searchedTag" id="organizationStatusValue" class="">
        <option value="Choose tag"></option>
          <?php		foreach ($organizationTags as $tag) { print_r($tag); ?>
          <option value="<?=$tag?>"<?php	if ($tag == $_REQUEST['searchedTag']) print " selected"; ?>><?=$tag?>
          </option>
          <?php		} ?>
      </select>
    </div>
    <div><label>Records per page:</label><input type="text" name="page_size" class="value input" style="width: 45px" value="<?=$organizations_per_page?>" /></div>
    <button id="searchOrganizationButton" name="btn_search" onclick="submitSearch(0)"/>Search</button>
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
          if (isset($greenbar)) $greenbar = ''; else $greenbar = " greenbar";
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
            <td colspan="5"><p>No Organizations Found</p></td>
      </div>
      <?php
      }
      ?>
  </div><!-- end table -->

        <!--    Standard Page Navigation Bar ADMIN ONLY -->
        <div class="pager_bar">
	        <div class="pager_controls">
		        <a href="/_register/organizations?start=0&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>&name=<?=$_REQUEST['name']?>&searchedTag=<?=$_REQUEST['searchedTag']?>&page_size=<?=$organizations_per_page?>" class="pager pagerFirst"><< First </a>
		        <a href="/_register/organizations?start=<?=$prev_offset?>&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>&name=<?=$_REQUEST['name']?>&searchedTag=<?=$_REQUEST['searchedTag']?>&page_size=<?=$organizations_per_page?>" class="pager pagerPrevious"><</a>
		        &nbsp;<?=$_REQUEST['start']+1?> - <?=$_REQUEST['start']+$organizations_per_page?> of <?=$total_organizations?>&nbsp;
		        <a href="/_register/organizations?start=<?=$next_offset?>&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>&name=<?=$_REQUEST['name']?>&searchedTag=<?=$_REQUEST['searchedTag']?>&page_size=<?=$organizations_per_page?>" class="pager pagerNext">></a>
		        <a href="/_register/organizations?start=<?=$last_offset?>&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>&name=<?=$_REQUEST['name']?>&searchedTag=<?=$_REQUEST['searchedTag']?>&page_size=<?=$organizations_per_page?>" class="pager pagerLast"> Last >></a>
            </div>
        </div>
    </form>
<?php		
    if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
?>
<form action="<?=PATH?>/_register/organization" method="get">
    <div class="button-bar"><span style="text-align: center"><input type="submit" name="button_submit" value="Add Organization" class="input button"/></span></div>
</form>
<?php	} ?>