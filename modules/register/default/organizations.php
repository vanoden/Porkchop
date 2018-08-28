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
	.label {
		text-align: left;
	}
	th.organizationsCodeLabel {
		width: 150px;
	}
	th.organizationsNameLabel {
		width: 540px;
	}
	td.value {
		overflow: hidden;
	}
	.greenbar {
		background-color: #bbbbbb;
	}
	a.pager {
		margin: 5px;
	}
	input.checkbox {
		height: 22px;
		width: 22px;
	}
	span.status {
		font-size: 20px;
	}
</style>
</script>
<form id="orgSearch" method="get" class="float: left">
<div class="title">Organizations</div>
<div id="search_container">
	<input type="text" id="searchOrganizationInput" name="name" value="<?=$_REQUEST['name']?>" class="value input searchInput"/>
	<input type="checkbox" name="hidden" class="checkbox" value="1"<? if ($_REQUEST['hidden']) print " checked"; ?> /><span class="status">Hidden</span>
	<input type="checkbox" name="expired" class="checkbox" value="1"<? if ($_REQUEST['expired']) print " checked"; ?> /><span class="status">Expired</span>
	<input type="checkbox" name="deleted" class="checkbox" value="1"<? if ($_REQUEST['deleted']) print " checked"; ?> /><span class="status">Deleted</span>
	<a href="#" id="searchOrganizationButton" name="btn_search" class="search_button" onclick="submitSearch(0)"/>&nbsp;</a>
	<input type="hidden" id="start" name="start" value="0">
</div>
<hr style="visibility: hidden">
<table cellpadding="0" cellspacing="0" class="body">
<tr><th class="label organizationsCodeLabel">ID</th>
	<th class="label organizationsNameLabel">Name</th>
	<th class="label organizationsCodeLabel">Status</th>
	<th class="label organizationsCodeLabel">Members</th>
</tr>
<?	foreach ($organizations as $organization) { ?>
<tr><td class="value<?=$greenbar?>"><a class="value<?=$greenbar?>" href="<?=PATH."/_register/organization?organization_id=".$organization->id?>"><?=$organization->code?></a></td>
	<td class="value<?=$greenbar?>"><?=$organization->name?></td>
	<td class="value<?=$greenbar?>"><?=$organization->status?></td>
	<td class="value<?=$greenbar?>"><?=$organization->activeCount()?></td>
</tr>
<?		if ($greenbar) $greenbar = '';
		else $greenbar = " greenbar";
	}
?>
<tr><td colspan="6" style="text-align: center">
	<a href="javascript:void(0)" class="pager pagerFirst" onclick="submitSearch(0)"><<</a>
	<a href="javascript:void(0)" class="pager pagerPrevious" onclick="submitSearch(<?=$prev_offset?>)"><</a>
	&nbsp;<?=$_REQUEST['start']+1?> - <?=$next_offset?> of <?=$total_organizations?>&nbsp;
	<a href="javascript:void(0)" class="pager pagerNext" onclick="submitSearch(<?=$next_offset?>)">></a>
	<a href="javascript:void(0)" class="pager pagerLast" onclick="submitSearch(<?=$last_offset?>)">>></a>
	</td>
</tr>
</table>
</form>
<?
	if ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
?>
<form action="<?=PATH?>/_register/organization" method="get">
<span style="text-align: center"><input type="submit" name="button_submit" value="Add Organization" class="input button"/></span>
<?	} ?>
</form>