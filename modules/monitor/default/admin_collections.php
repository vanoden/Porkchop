<style>
.collectionNameColumn {	width: 20%;	overflow: hidden;	}
.collectionCustomerColumn {	width: 15%; }
.collectionStatusColumn {	width: 5%;	overflow: hidden;	}
.collectionStartedColumn { width: 24%; }
.collectionFinishedColumn {	width: 24%;	}
.collectionDeleteColumn {	width: 6%;}
</style>
<script language="Javascript">
	function newCollection() {
		window.location = "/_monitor/dashboard";
	}
	function deleteCollection(collectionID) {
		collectionName = document.getElementById('Collection['+collectionID+']').innerHTML;
		var r = confirm("Delete Job '"+collectionName+"'?");
		if (r == true) {
			document.getElementById('delete_collection').value = collectionID;
			collectionsForm.submit();
			return true;
		}
		else {
			return false;
		}
	}
	function sort(column) {
		if (document.getElementById('sort').value == column) {
			document.getElementById('sort_order').value = 'DESC';
			console.log('Sorting report in descending order by '+column);
		}
		else {
			document.getElementById('sort_order').value = 'ASC';
			console.log('Sorting report in ascending order by '+column);
		}
		document.getElementById('sort').value = column;
		document.getElementById('collectionsForm').submit();
		return true;
	}
	function submitSearch(start) {
		document.getElementById('start').value=start;
		document.getElementById('collectionsForm').submit();
		return true;
	}
</script>
<?	if ($page->error) { ?>
<div class="form_error"><?=$page->error?></div>
<?	} ?>
<?	if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?	} ?>
<form name="collectionsForm" method="post" id="collectionsForm">
<input id="delete_collection" type="hidden" name="delete_collection" value=""/>
<input id="sort" type="hidden" name="sort" value="<?=$_REQUEST['sort']?>"/>
<input id="sort_order" type="hidden" name="sort_order" value="<?=$_REQUEST['sort_order']?>"/>
<input type="hidden" id="start" name="start" value="0">
<h2>Filters</h2>
<div class="table-narrow">
<table class="body" cellpadding="0" cellspacing="0">
<tr>
	<th class="label">Organization</th>
	<th class="label">Status</th>
	<th class="label">Started After</th>
	<th class="label">Started Before</th>
</tr>
<tr><td class="value">
		<select name="organization_id" class="value input">
			<option value="">All</option>
<?	foreach ($organizations as $organization) { ?>
			<option value="<?=$organization->id?>"<? if (isset($_REQUEST['organization_id']) && $organization->id == $_REQUEST['organization_id']) print " selected"; ?>><?=$organization->name?></option>
<?	} ?>
		</select>
	</td>
	<td class="value">
		<select name="status" class="value input">
			<option value="">All</option>
			<option value="NEW"<? if ($_REQUEST['status'] == "NEW") print " selected"; ?>>NEW</option>
			<option value="ACTIVE"<? if ($_REQUEST['status'] == "ACTIVE") print " selected"; ?>>ACTIVE</option>
			<option value="COMPLETE"<? if ($_REQUEST['status'] == "COMPLETE") print " selected"; ?>>COMPLETE</option>
			<option value="DELETED"<? if ($_REQUEST['status'] == "DELETED") print " selected"; ?>>DELETED</option>
		</select>
	</td>
	<td class="value"><input type="text" name="date_start" class="value input" value="<?=$_REQUEST['date_start']?>"/></td>
	<td class="value"><input type="text" name="date_end" class="value input" value="<?=$_REQUEST['date_end']?>"/></td>
</tr>
</table>
<div class="button-bar"><input type="submit" name="btn_search" class="button" /></div>
</div>
<h3>Jobs [<?=count($collections)?>]</h3>
<table class="body monitorCollectionsBody" cellpadding="0" cellspacing="0">
<tr>
	<th class="label columnLabel collectionNameColumn"><a href="javascript:void(0)" class="label" onclick="sort('name');">Name</a></th>
	<th class="label columnLabel collectionCustomerColumn"><a href="javascript:void(0)" class="label" onclick="sort('organization');">Organization</a></th>
	<th class="label columnLabel collectionStatusColumn"><a href="javascript:void(0)" class="label" onclick="sort('status');">Status</a></th>
	<th class="label columnLabel collectionStartedColumn"><a href="javascript:void(0)" class="label" onclick="sort('date_start');">Started</a></th>
	<th class="label columnLabel collectionFinishedColumn"><a href="javascript:void(0)" class="label" onclick="sort('date_end');">Finished</a></th>
	<th class="label columnLabel collectionDeleteColumn">Delete</td>
</tr>
<?	foreach ($collections as $collection)
	{
		if (! $collection->name) $collection->name = "[none]";
		
?>
<tr><td class="value columnValue collectionNameColumn<?=$greenbar?>"><a href="/_monitor/dashboard/<?=$collection->code?>" id="Collection[<?=$collection->id?>]"><?=$collection->name?></a></td>
	<td class="value columnValue collectionCustomerColumn<?=$greenbar?>"><?=$collection->organization->name?></td>
	<td class="value columnValue collectionCustomerColumn<?=$greenbar?>"><?=$collection->status?></td>
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
<!--    Standard Page Navigation Bar ADMIN ONLY -->
<div class="pager_bar">
	<div class="pager_controls">
		<a href="javascript:void(0)" class="pager pagerFirst" onclick="submitSearch(0)"><<</a>
		<a href="javascript:void(0)" class="pager pagerPrevious" onclick="submitSearch(<?=$prev_offset?>)"><</a>
		&nbsp;<?=$_REQUEST['start']+1?> - <?=$next_offset?> of <?=$total_collections?>&nbsp;
		<a href="javascript:void(0)" class="pager pagerNext" onclick="submitSearch(<?=$next_offset?>)">></a>
		<a href="javascript:void(0)" class="pager pagerLast" onclick="submitSearch(<?=$last_offset?>)">>></a>
	</div>
</div>
</form>
