<script src="/js/sort.js"></script>
<script>
    // document loaded - start table sort
    window.addEventListener('DOMContentLoaded', (event) => {     
        <?php
        $sortDirection = 'desc';
        if ($_REQUEST['sort_direction'] == 'desc') $sortDirection = 'asc';
        
		switch ($_REQUEST['sort_by']) {   
            case 'code':
                ?>
                SortableTable.sortColumn('code-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;       
            case 'date_request':
                ?>
                SortableTable.sortColumn('date-requested-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;        
            case 'customer_id':
                ?>
                SortableTable.sortColumn('requested-by-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            case 'organization_id':
                ?>
                SortableTable.sortColumn('organization-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            case 'type':
                ?>
                SortableTable.sortColumn('type-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            case 'status':
                ?>
                SortableTable.sortColumn('status-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            default:
                ?>
                SortableTable.sortColumn('code-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
		}
        ?>
    });
</script>
<div style="width: 756px;">
	<div class="breadcrumbs">
		Support Home
	</div>
</div>

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

<h2 style="display: inline-block;"><i class='fa fa-list-ol' aria-hidden='true'></i> Support Requests <?=!empty($_REQUEST['btn_all']) ? '[ALL]' : '[Open]';?></h2>
<?php include(MODULES.'/support/partials/search_bar.php'); ?>
<form id="pageForm" method='get'>
    <input id="sort_by" type="hidden" name="sort_by" value="" />
    <input id="sort_direction" type="hidden" name="sort_direction" value="<?=($_REQUEST['sort_direction'] == 'desc') ? 'asc': 'desc';?>" />
    <table>
        <tr>
            <th id="code-sortable-column" class="sortableHeader" onclick="document.getElementById('sort_by').value = 'code'; document.getElementById('pageForm').submit();">Code</th>
            <th id="date-requested-sortable-column" class="sortableHeader" onclick="document.getElementById('sort_by').value = 'date_request'; document.getElementById('pageForm').submit();">Date Requested</th>
            <th id="requested-by-sortable-column" class="sortableHeader" onclick="document.getElementById('sort_by').value = 'customer_id'; document.getElementById('pageForm').submit();">Requested By</th>
            <th id="organization-sortable-column" class="sortableHeader" onclick="document.getElementById('sort_by').value = 'organization_id'; document.getElementById('pageForm').submit();">Organization</th>
            <th id="type-sortable-column" class="sortableHeader" onclick="document.getElementById('sort_by').value = 'type'; document.getElementById('pageForm').submit();">Type</th>
            <th id="status-sortable-column" class="sortableHeader" onclick="document.getElementById('sort_by').value = 'status'; document.getElementById('pageForm').submit();">Status</th>
        </tr>
        <?php	foreach ($requests as $request) { ?>
            <tr><td><a href="/_support/request_detail/<?=$request->code?>"><?=$request->code?></a></td>
                <td><?=$request->date_request?></td>
                <td><?=$request->customer->first_name?> <?=$request->customer->last_name?></td>
                <td><?=$request->customer->organization->name?></td>
                <td><?=$request->type?></td>
                <td><?=$request->status?></td>
            </tr>
        <?php	} ?>
    </table>
</form>
