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
<span class="title">Organizations</span>

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

    <form id="orgSearch" method="get" class="float: left">
        <div id="search_container">
	        <input type="text" id="searchOrganizationInput" name="name" placeholder="organization name" value="<?=isset($_REQUEST['name']) ? $_REQUEST['name']: ''?>" class="value input searchInput wide_md"/>
	        <input type="checkbox" name="hidden" class="checkbox" value="1" <?php if (!empty($_REQUEST['hidden'])) print "checked"; ?> /><span class="status">Hidden</span>
	        <input type="checkbox" name="expired" class="checkbox" value="1" <?php if (!empty($_REQUEST['expired'])) print "checked"; ?> /><span class="status">Expired</span>
	        <input type="checkbox" name="deleted" class="checkbox" value="1" <?php if (!empty($_REQUEST['deleted'])) print "checked"; ?> /><span class="status">Deleted</span>
	        <input type="hidden" id="start" name="start" value="0">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Filter by Tag: <select name="searchedTag" id="organizationStatusValue" class="">
                <option value=""></option>
	            <?php		foreach ($organizationTags as $tag) { print_r($tag); ?>
	            <option value="<?=$tag?>"<?php	if ($tag == $_REQUEST['searchedTag']) print " selected"; ?>><?=$tag?></option>
	            <?php		} ?>
            </select>
            &nbsp;&nbsp;&nbsp;&nbsp;<button id="searchOrganizationButton" name="btn_search" onclick="submitSearch(0)"/>Search</button>
        </div>
        <hr style="visibility: hidden">
        <table cellpadding="0" cellspacing="0" class="body">
            <tr><th class="label organizationsCodeLabel">ID</th>
	            <th class="label organizationsNameLabel">Name</th>
	            <th class="label organizationsCodeLabel">Status</th>
	            <th class="label organizationsCodeLabel">Members</th>
            </tr>
            <?php
            foreach ($organizations as $organization) { 
                if (isset($greenbar)) $greenbar = ''; else $greenbar = " greenbar";
            ?>
            <tr><td class="value<?=$greenbar?>"><a class="value<?=$greenbar?>" href="<?=PATH."/_register/organization?organization_id=".$organization->id?>"><?=$organization->code?></a></td>
	            <td class="value<?=$greenbar?>"><?=$organization->name?></td>
	            <td class="value<?=$greenbar?>"><?=$organization->status?></td>
	            <td class="value<?=$greenbar?>"><?=$organization->activeCount()?></td>
            </tr>
            <?php
            }
            if (!count($organizations)) {
            ?>
                <tr>
	                <td colspan="4"><p>No Organizations Found</p></td>
                </tr>
            <?php
            }
            ?>
        </table>
        <!--    Standard Page Navigation Bar ADMIN ONLY -->
        <div class="pager_bar">
	        <div class="pager_controls">
		        <a href="/_register/organizations?start=0&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>" class="pager pagerFirst"><< First </a>
		        <a href="/_register/organizations?start=<?=$prev_offset?>&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>" class="pager pagerPrevious"><</a>
		        &nbsp;<?=$_REQUEST['start']+1?> - <?=$_REQUEST['start']+$organizations_per_page?> of <?=$total_organizations?>&nbsp;
		        <a href="/_register/organizations?start=<?=$next_offset?>&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>" class="pager pagerNext">></a>
		        <a href="/_register/organizations?start=<?=$last_offset?>&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>" class="pager pagerLast"> Last >></a>
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