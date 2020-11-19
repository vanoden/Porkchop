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
	a.pager {	margin: 5px; }
</style>
</script>

<section>
	<article class="segment">
		<h2>Organizations</h2>
        <?php	 if ($page->errorCount() > 0) { ?>
            <div class="form_error"><?=$page->errorString()?></div>
        <?php	 } ?>
		
<form id="orgSearch" method="get" class="float: left">
<div id="search_container">
	<input type="text" id="searchOrganizationInput" name="name" value="<?=$_REQUEST['name']?>" class="value input searchInput wide_md"/>
	<a href="#" id="searchOrganizationButton" name="btn_search" class="search_button" onclick="submitSearch(0)"/>&nbsp;</a>
	<input type="checkbox" name="hidden" class="checkbox" value="1" <?php if ($_REQUEST['hidden']) print "checked"; ?> /><span class="status">Hidden</span>
	<input type="checkbox" name="expired" class="checkbox" value="1" <?php if ($_REQUEST['expired']) print "checked"; ?> /><span class="status">Expired</span>
	<input type="checkbox" name="deleted" class="checkbox" value="1" <?php if ($_REQUEST['deleted']) print "checked"; ?> /><span class="status">Deleted</span>
	
	<input type="hidden" id="start" name="start" value="0">
</div>
<hr style="visibility: hidden">
<table cellpadding="0" cellspacing="0" class="body">
<tr><th class="label organizationsCodeLabel">ID</th>
	<th class="label organizationsNameLabel">Name</th>
	<th class="label organizationsCodeLabel">Status</th>
	<th class="label organizationsCodeLabel">Members</th>
</tr>
<?php	foreach ($organizations as $organization) { ?>
<tr><td class="value<?=$greenbar?>"><a class="value<?=$greenbar?>" href="<?=PATH."/_register/organization?organization_id=".$organization->id?>"><?=$organization->code?></a></td>
	<td class="value<?=$greenbar?>"><?=$organization->name?></td>
	<td class="value<?=$greenbar?>"><?=$organization->status?></td>
	<td class="value<?=$greenbar?>"><?=$organization->activeCount()?></td>
</tr>
<?php		if ($greenbar) $greenbar = '';
		else $greenbar = " greenbar";
	}
?>
</table>
<!--    Standard Page Navigation Bar ADMIN ONLY -->
<div class="pager_bar">
	<div class="pager_controls">
		<a href="/_register/accounts?start=0&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>" class="pager pagerFirst"><< First </a>
		<a href="/_register/accounts?start=<?=$prev_offset?>&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>" class="pager pagerPrevious"><</a>
		&nbsp;<?=$_REQUEST['start']+1?> - <?=$_REQUEST['start']+$customers_per_page+1?> of <?=$total_customers?>&nbsp;
		<a href="/_register/accounts?start=<?=$next_offset?>&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>" class="pager pagerNext">></a>
		<a href="/_register/accounts?start=<?=$last_offset?>&hidden=<?=$_REQUEST['hidden']?>&deleted=<?=$_REQUEST['deleted']?>&expired=<?=$_REQUEST['expired']?>" class="pager pagerLast"> Last >></a>
    </div>
</div>
</form>

<?php		if ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
?>
<form action="<?=PATH?>/_register/organization" method="get">
<div class="button-bar"><span style="text-align: center"><input type="submit" name="button_submit" value="Add Organization" class="input button"/></span></div>
<?php	} ?>
</form>
