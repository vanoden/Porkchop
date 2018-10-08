<style>
	.collectionNameColumn {
		width: 300px;
		overflow: hidden;
	}
	.collectionCustomerColumn {
		width: 240px;
	}
	.collectionStartedColumn {
		width: 130px;
	}
	.collectionFinishedColumn {
		width: 130px;
	}
	.collectionDeleteColumn {
		width: 35px;
	}
	#btn_new_collection {
		width: 145px;
		position: absolute;
		top: 6px;
		left: 260px;
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
		var r = confirm("Delete Job '"+collectionName+"'?");
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
<div class="title">Jobs</div>
<div><input type="button" id="btn_new_collection" name="btn_new" class="button" onclick="newCollection()" value="New Job" /></div>
<table class="body monitorCollectionsBody" cellpadding="0" cellspacing="0">
<tr><th class="label columnLabel columnLabelLeft collectionNameColumn">Name</th>
	<th class="label columnLabel collectionCustomerColumn">Customer</th>
	<th class="label columnLabel collectionStartedColumn">Started</th>
	<th class="label columnLabel collectionFinishedColumn">Finished</th>
	<th class="label columnLabel columnLabelRight collectionDeleteColumn">Delete</th>
</tr>
<?	foreach ($collections as $collection) {
		$name = $collection->metadata('name');
		if (! $name) $name = "[none]";
		
?>
<tr><td class="value columnValue columnValueLeft collectionNameColumn<?=$greenbar?>"><a href="/_monitor/dashboard/<?=$collection->code?>" id="Collection[<?=$collection->id?>]"><?=$name?></a></td>
	<td class="value columnValue collectionCustomerColumn<?=$greenbar?>"><?=$collection->metadata('customer')?></td>
	<td class="value columnValue collectionStartedColumn<?=$greenbar?>"><?=date("m/d/y H:m",$collection->timestamp_start)?></td>
	<td class="value columnValue collectionFinishedColumn<?=$greenbar?>"><?=date("m/d/y H:m",$collection->timestamp_end)?></td>
	<td class="value columnValue columnValueRight collectionDeleteColumn<?=$greenbar?>"><input type="image" src="/img/_global/icon_trashcan.svg" name="delete_collection" onclick="deleteCollection(<?=$collection->id?>)" /></td>
</tr>
<?
		if ($greenbar) $greenbar = "";
		else $greenbar = " greenbar";
	}
?>
</table>
</form>
