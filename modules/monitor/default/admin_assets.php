<?php		
    if (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin')){
        print "<span class=\"form_error\">You are not authorized for this view!</span>";
        return;
    }
?>			
<script language="Javascript">
	function goNewMonitor() {
		var form = document.getElementById('filterForm');
		form.action = '/_monitor/admin_details';
		form.submit();
		return true;
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
		document.getElementById('filterForm').submit();
		return true;
	}
	function submitSearch(start) {
		document.getElementById('start').value=start;
		document.getElementById('filterForm').submit();
		return true;
	}
</script>
<?php	 if ($page->errorCount() > 0) { ?>
    <div class="form_error"><?=$page->errorString()?></div>
<?php	 } ?>
<form id="filterForm" name="filterForm" method="get" action="/_monitor/admin_assets">
    <input id="sort" type="hidden" name="sort" value="<?=$_REQUEST['sort']?>"/>
    <input id="sort_order" type="hidden" name="sort_order" value="<?=$_REQUEST['sort_order']?>"/>
    <input type="hidden" id="start" name="start" value="0">
    <h2>Filter</h2>
    <table class="body" style="width: 700px">
    <tr>
	    <th class="label">Serial Number</th>
	    <th class="label">Model</th>
	    <th class="label">Organization</th>
    </tr>
    <tr><td class="value"><input type="text" name="code" value="<?=$_REQUEST['code']?>" class="value input" autofocus/></td>
	    <td class="value">
		    <select name="product_id" class="value input">
			    <option value="">All</option>
    <?php	foreach ($products as $product) { ?>
			    <option value="<?=$product->id?>"<?php	if (isset($_REQUEST['product_id']) && $product->id == $_REQUEST['product_id']) print " selected";?>><?=$product->code?></option>
    <?php	} ?>
		    </select>
	    </td>
	    <td class="value">
		    <select name="organization_id" class="value input">
			    <option value="">All</option>
    <?php	foreach ($organizations as $organization) { ?>
			    <option value="<?=$organization['id']?>"<?php	if (key_exists('organization_id',$_REQUEST) && $organization['id'] == $_REQUEST['organization_id']) print " selected";?>><?=$organization['name']?></option>
    <?php	} ?>
		    </select>
	    </td>
    </tr>
    <tr><td colspan="3" class="form_footer">
		    <input type="submit" name="btn_submit" value="Search" class="button" />
		    <input type="button" name="btn_new" value="Add" class="button" onclick="goNewMonitor()" />
	    </td>
    </tr>
    </table>
    <br/>
	    <h2>Monitors [<?=$total_assets?>]</h2>
    <table class="body">
    <tr>
	    <th class="label"><a href="javascript:void(0)" class="label columnLabel" onclick="sort('serial');">Serial</a></th>
	    <th class="label"><a href="javascript:void(0)" class="label columnLabel" onclick="sort('product');">Model</a></th>
	    <th class="label"><a href="javascript:void(0)" class="label columnLabel" onclick="sort('organization');">Organization</a></th>
	    <th class="label">Last Comm</th>
	    <th class="label">Result</th>
    </tr>
    <?php	$greenbar = '';
	    foreach ($assets as $asset) {
	        if ($greenbar) $greenbar = ''; else $greenbar = 'greenbar';
	        $communication = $asset->last_communication();
	        if (isset($communication)) {
		        $last_hit = $communication->session->last_hit_date;
		        if ($communication->response->success) $result = "Success";
		        else $result = "Error";
	        } else {
		        $last_hit = "Never";
		        $result = "";
	        }
    ?>
    <tr>
        <td class="value <?=$greenbar?>"><a href="/_monitor/admin_details/<?=$asset->code?>/<?=$asset->product->code?>" class="value"><?=$asset->code?></a></td>
	    <td class="value <?=$greenbar?>"><?=$asset->product->code?></td>
	    <td class="value <?=$greenbar?>"><?=$asset->organization->name?></td>
	    <td class="value <?=$greenbar?>"><?=$last_hit?></td>
	    <td class="value <?=$greenbar?>"><?=$result?></td>
    </tr>
    <?php						
	    }
    ?>
    </table>
    <!--    Standard Page Navigation Bar ADMIN ONLY -->
    <div class="pager_bar">
	    <div class="pager_controls">
		    <a href="javascript:void(0)" class="pager pagerFirst" onclick="submitSearch(0)"><<</a>
		    <a href="javascript:void(0)" class="pager pagerPrevious" onclick="submitSearch(<?=$prev_offset?>)"><</a>
		    &nbsp;<?=$_REQUEST['start']+1?> - <?=$next_offset?> of <?=$total_assets?>&nbsp;
		    <a href="javascript:void(0)" class="pager pagerNext" onclick="submitSearch(<?=$next_offset?>)">></a>
		    <a href="javascript:void(0)" class="pager pagerLast" onclick="submitSearch(<?=$last_offset?>)">>></a>
	    </div>
    </div>
	<!--    [end] Standard Page Navigation Bar ADMIN ONLY-->
</form>
