<?  if (! $GLOBALS['_SESSION_']->customer->has_role('register reporter')) {
		print "<span class=\"form_error\">You are not authorized for this view!</span>";
		return;
	}
?>
<script type="text/javascript">
	function submitForm()
	{
		return true;
	}
	function submitSearch(start)
	{
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
	#searchOrganizationInput {
		margin-left: 100px;
	}
	.search_button {
		width: 30px;
		height: 30px;
		background-image: url(/images/magnify_glass.png);
		margin-top: 8px;
		padding: 0px;
	}
	a.pager {
		margin: 5px;
	}
</style>
</script>
<form id="orgSearch" method="get" class="float: left">
<span class="title">Organizations</span>
<input type="text" id="searchOrganizationInput" name="name" value="<?=$_REQUEST['name']?>" class="value input"/>
<input type="hidden" id="start" name="start" value="0">
<img id="searchOrganizationButton" name="btn_search" class="search_button" onclick="submitSearch(0)"/>
<table cellpadding="0" cellspacing="0" class="body">
<tr><th class="label organizationsCodeLabel">ID</th>
	<th class="label organizationsNameLabel">Name</th>
</tr>
<?	foreach ($organizations as $organization) { ?>
<tr><td class="value<?=$greenbar?>"><a class="value<?=$greenbar?>" href="<?=PATH."/_register/organization?organization_id=".$organization->id?>"><?=$organization->code?></a></td>
	<td class="value<?=$greenbar?>"><?=$organization->name?></td>
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