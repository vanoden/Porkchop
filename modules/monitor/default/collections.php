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
		} else {
			return false;
		}
	}
	
	function submitSearch(start) {
		document.getElementById('start').value=start;
		document.forms[0].submit();
		return true;
	}
</script>
<?php	if ($GLOBALS['_page']->error) { ?>
    <div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?php	} ?>
<?php	if ($GLOBALS['_page']->success) { ?>
    <div class="form_success"><?=$GLOBALS['_page']->success?></div>
<?php	} ?>
<form name="collectionsForm" method="post">
    <input id="delete_collection" type="hidden" name="delete_collection" value=""/>
    <input type="hidden" id="start" name="start" value="0">
    <div class="title"><?=$total_collections?> Jobs</div>
    <div><input type="button" id="btn_new_collection" name="btn_new" class="button" onclick="newCollection()" value="New Job" /></div>
    <table class="body monitorCollectionsBody" cellpadding="0" cellspacing="0">
    <tr><th class="label columnLabel columnLabelLeft collectionNameColumn">Name</th>
	    <th class="label columnLabel collectionCustomerColumn">Customer</th>
	    <th class="label columnLabel collectionStatusColumn">Status</th>
	    <th class="label columnLabel collectionStartedColumn">Started</th>
	    <th class="label columnLabel collectionFinishedColumn">Finished</th>
	    <th class="label columnLabel columnLabelRight collectionDeleteColumn"><span class="mobile-hide">Delete</span><span class="mobile-show"><img src="/img/_global/icon_trashcan.svg"></span></th>
    </tr>
    <?php	foreach ($collections as $collection) {
		    $name = $collection->metadata('name');
		    if (! $name) $name = "[none]";
		    
    ?>
    <tr><td class="value columnValue columnValueLeft collectionNameColumn<?=$greenbar?>"><a href="/_monitor/dashboard/<?=$collection->code?>" id="Collection[<?=$collection->id?>]"><?=$name?></a></td>
	    <td class="value columnValue collectionCustomerColumn<?=$greenbar?>"><?=$collection->metadata('customer')?></td>
	    <td class="value columnValue collectionStatusColumn<?=$greenbar?>"><?=$collection->status?></td>
	    <td class="value columnValue collectionStartedColumn<?=$greenbar?>"><?=date("m/d/y H:m",$collection->timestamp_start)?></td>
	    <td class="value columnValue collectionFinishedColumn<?=$greenbar?>"><?=date("m/d/y H:m",$collection->timestamp_end)?></td>
	    <td class="value columnValue columnValueRight collectionDeleteColumn<?=$greenbar?>"><input type="button" name="delete_collection" value="x" onclick="deleteCollection(<?=$collection->id?>)" /></td>
    </tr>
    <?php			if ($greenbar) $greenbar = "";
		    else $greenbar = " greenbar";
	    }
    ?>
    </table>
    <div class="pager_bar">
	    <div class="pager_controls">
		    <a href="javascript:void(0)" class="pager pagerFirst" onclick="submitSearch(0)"><< First </a>
		    <a href="javascript:void(0)" class="pager pagerPrevious" onclick="submitSearch(<?=$prev_offset?>)"><</a>
		    &nbsp;<?=$_REQUEST['start']+1?> - <?=$next_offset?> of <?=$total_collections?>&nbsp;
		    <a href="javascript:void(0)" class="pager pagerNext" onclick="submitSearch(<?=$next_offset?>)">></a>
		    <a href="javascript:void(0)" class="pager pagerLast" onclick="submitSearch(<?=$last_offset?>)"> Last >></a>
	    </div>
    </div>
</form>
