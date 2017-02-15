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
</script>
<?	if ($GLOBALS['_page']->error) { ?>
<div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?	} ?>
<?	if ($GLOBALS['_page']->success) { ?>
<div class="form_success"><?=$GLOBALS['_page']->success?></div>
<?	} ?>
<form name="collectionsForm" method="post">
<input id="delete_collection" type="hidden" name="delete_collection" value=""/>
<table class="body monitorCollectionsBody" cellpadding="0" cellspacing="0">
<tr><td colspan="4" class="title">Jobs</td></tr>
<tr><td style="text-align: center" colspan="4">
	<input type="button" name="btn_new" class="button" onclick="newCollection()" value="New Collection" />
</td>
</tr>
<tr><td class="label columnLabel collectionNameColumn">Name</td>
	<td class="label columnLabel collectionCustomerColumn">Customer</td>
	<td class="label columnLabel collectionStartedColumn">Started</td>
	<td class="label columnLabel collectionFinishedColumn">Finished</td>
	<td class="label columnLabel collectionDeleteColumn">Delete</td>
</tr>
<?	foreach ($collections as $collection) {
		if (! $collection->name) $collection->name = "[none]";
		
?>
<tr><td class="value columnValue collectionNameColumn<?=$greenbar?>"><a href="/_monitor/dashboard/<?=$collection->code?>" id="Collection[<?=$collection->id?>]"><?=$collection->name?></a></td>
	<td class="value columnValue collectionCustomerColumn<?=$greenbar?>"><?=$collection->metadata('customer')?></td>
	<td class="value columnValue collectionStartedColumn<?=$greenbar?>"><?=date("Y-m-d H:m",$collection->timestamp_start)?></td>
	<td class="value columnValue collectionFinishedColumn<?=$greenbar?>"><?=date("Y-m-d H:m",$collection->timestamp_end)?></td>
	<td class="value columnValue collectionDeleteColumn<?=$greenbar?>" style="text-align: center"><input type="button" style="padding-left: 2px; padding-right: 2px; padding-top: 0px; height: 18px; font-weight: bold" name="delete_collection" value="x" onclick="deleteCollection(<?=$collection->id?>)" /></td>
</tr>
<?
		if ($greenbar) $greenbar = "";
		else $greenbar = " greenbar";
	}
?>
</table>
</form>