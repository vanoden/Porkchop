<style>
	.collectionNameColumn {
		width: 330px;
		overflow: hidden;
	}
	.collectionCustomerColumn {
		width: 250px;
	}
	.collectionStartedColumn {
		width: 115px;
	}
	.collectionFinishedColumn {
		width: 115px;
	}
	.collectionDeleteColumn {
		width: 35px;
	}
	.value {
		font-size: 12px;
	}
</style>
<script language="Javascript">
	function newCollection()
	{
		window.location = "/_monitor/dashboard";
	}
	function deleteCollection(collectionID)
	{
		collectionName = document.getElementById('Collection['+collectionID+']').innerHTML;
		var r = confirm("Delete Collection '"+collectionName+"'?");
		if (r == true)
		{
			document.getElementById('delete_collection').value = collectionID;
			collectionsForm.submit();
			return true;
		}
		else
		{
			return false;
		}
	}
	function loadCollection(collectionID)
	{
		document.forms[0].action = "/_monitor/dashboard";
		document.forms[0].collection_id.value = collectionID;
		document.forms[0].submit();
		return true;
	}
	function submitSearch(start)
	{
		document.getElementById('start').value=start;
		document.forms[0].submit();
		return true;
	}
</script>
<?	if ($GLOBALS['_page']->error) { ?>
<div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?	} ?>
<?	if ($GLOBALS['_page']->success) { ?>
<div class="form_success"><?=$GLOBALS['_page']->success?></div>
<?	} ?>
<form name="collectionsForm" method="post">
<input id="delete_collection" type="hidden" name="delete_collection" value=""/>
<input id="collection_id" type="hidden" name="collection_id" />
<input id="start" type="hidden" name="start" />
<div class="title">Filters</div>
<table class="body" cellpadding="0" cellspacing="0" style="width: 1000px">
<tr><td class="label">Organization</td>
	<td class="label">Started After</td>
	<td class="label">Started Before</td>
</tr>
<tr><td class="value">
		<select name="organization_id" class="value input">
			<option value="">All</option>
<?	foreach ($organizations as $organization) { ?>
			<option value="<?=$organization->id?>"<? if ($organization->id == $_REQUEST['organization_id']) print " selected"; ?>><?=$organization->name?></option>
<?	} ?>
		</select>
	</td>
	<td class="value"><input type="text" name="date_start" class="value input" value="<?=$_REQUEST['date_start']?>"/></td>
	<td class="value"><input type="text" name="date_end" class="value input" value="<?=$_REQUEST['date_end']?>"/></td>
</tr>
<tr><td colspan="3" class="form_footer"><input type="submit" name="btn_search" class="button" /></td></tr>
</table>
<br>
<div class="title">Jobs [<?=$total_collections?>]</div>
<table class="body monitorCollectionsBody" cellpadding="0" cellspacing="0" style="width: 1000px">
<tr><td class="label columnLabel collectionNameColumn">Name</td>
	<td class="label columnLabel collectionCustomerColumn">Organization</td>
	<td class="label columnLabel collectionCustomerColumn">Customer</td>
	<td class="label columnLabel collectionStartedColumn">Started</td>
	<td class="label columnLabel collectionFinishedColumn">Finished</td>
	<td class="label columnLabel collectionDeleteColumn">Delete</td>
</tr>
<?	foreach ($collections as $collection)
	{
		$collection_name = $collection->metadata('name');
		if (! $collection_name) $collection_name = "[none]";
		
?>
<tr><td class="value columnValue collectionNameColumn<?=$greenbar?>"><a href="javascript:void(0)" onclick="loadCollection(<?=$collection->id?>)" id="Collection[<?=$collection->id?>]"><?=$collection_name?></a></td>
	<td class="value columnValue collectionCustomerColumn<?=$greenbar?>"><?=$collection->organization->name?></td>
	<td class="value columnValue collectionCustomerColumn<?=$greenbar?>"><?=$collection->customer?></td>
	<td class="value columnValue collectionStartedColumn<?=$greenbar?>"><?=date("Y-m-d H:m",$collection->timestamp_start)?></td>
	<td class="value columnValue collectionFinishedColumn<?=$greenbar?>"><?=date("Y-m-d H:m",$collection->timestamp_end)?></td>
	<td class="value columnValue collectionDeleteColumn<?=$greenbar?>" style="text-align: center"><input type="button" style="padding-left: 2px; padding-right: 2px; padding-top: 0px; height: 18px; font-weight: bold" name="delete_collection" value="x" onclick="deleteCollection(<?=$collection->id?>)" /></td>
</tr>
<?
		if ($greenbar) $greenbar = "";
		else $greenbar = " greenbar";
	}
?>
<tr><td colspan="6" style="text-align: center">
	<a href="javascript:void(0)" class="pager pagerFirst" onclick="submitSearch(0)"><<</a>
	<a href="javascript:void(0)" class="pager pagerPrevious" onclick="submitSearch(<?=$prev_offset?>)"><</a>
	&nbsp;<?=$_REQUEST['start']+1?> - <?=$next_offset?> of <?=$total_collections?>&nbsp;
	<a href="javascript:void(0)" class="pager pagerNext" onclick="submitSearch(<?=$next_offset?>)">></a>
	<a href="javascript:void(0)" class="pager pagerLast" onclick="submitSearch(<?=$last_offset?>)">>></a>
	</td>
</tr>
</table>
</form>