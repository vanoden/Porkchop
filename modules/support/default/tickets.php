<script src="/js/sort.js"></script>
<script>
    // document loaded - start table sort
    window.addEventListener('DOMContentLoaded', (event) => {     
        <?php
        $sortDirection = 'desc';
        if ($_REQUEST['sort_direction'] == 'desc') $sortDirection = 'asc';
        
		switch ($parameters['sort_by']) {   
            case 'requested':
                ?>
                SortableTable.sortColumn('date-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;       
            case 'product':
                ?>
                SortableTable.sortColumn('product-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            case 'serial':
                ?>
                SortableTable.sortColumn('serial-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            case 'status':
                ?>
                SortableTable.sortColumn('status-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
            default:
                ?>
                SortableTable.sortColumn('ticket-sortable-column', '<?=($_REQUEST['sort_direction'] == 'desc') ? 'up': 'down';?>');
                <?php
            break;
		}
        ?>
    });

    // update report from UI change
	function updateReport() {
		var pageForm = document.getElementById('pageForm');	
		pageForm.filtered.value = 1;
		pageForm.submit();
		return true;
	}
	
	// date picker with max date being current day
    window.onload = function() {
       $("#datepicker").datepicker({
            onSelect: function(dateText, inst) {
                var minDate = document.getElementById('min_date');
                minDate.value = dateText;
                updateReport();
            }, 
            maxDate: '0'
        });
    }
</script>
<style>
	body {font-size: 10px;}
span.label { margin: 3px 6px 3px 0px; font-weight: 600; font-size: 1rem;}
/*.checkbox-row span.value { text-transform: capitalize; font-weight: 600; margin: 0 15px 0 4px; font-size: 0.8rem;}*/

.table-flex { display: flex; }
.tableBody { max-width: none;}
.forms-filter {background: rgba(170,186,180,0.15); padding: 20px; }
a.button { margin: 1rem 0 1rem; background: #2FC61E; color: white; font-weight: 400; border: none; border-radius: 4px; padding: .4rem .6rem; font-size: .8rem;}
a.button:hover { background: #21b910;}
a.button.btn-secondary { background: #8b8f8b; }
a.button.btn-secondary:hover { background: #686c68;}
#page-mgmt input, input, input[type=text], #page-mgmt select { padding: 0.1rem 0.4rem; background: rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.1); border-radius: 0; -khtml-border-radius: 0; box-shadow: none; margin: .25rem 0 0.8rem; font-size: 0.8rem; font-weight: 400; min-height: 1.6rem; width: 50%; -webkit-appearance: none; -moz-appearance: none;}
#page-mgmt input[type=checkbox] { -webkit-appearance: checkbox; width: auto; }
.marginBottom2rem { margin-bottom: 2rem; }

span.value, .tableRowHeader > *, span.label, #page-mgmt input, input, input[type=text] { font-size: 0.8rem;}
/* #pageForm > .forms-filter > span.value, #pageForm > .forms-filter > span.value {display: inline; width: 50%; } */
input[type="text"], input[type=text], span.label { display: inline; }
.hiddenMobile { display: none;}

@media (min-width:450px) {
	body {font-size: 16px;}
	.flex-space-between {display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center;}
	.flex-space-between.form-basis > * {flex: 1 0 140px; }
	span.value, span.label,  { font-size: 1rem; }
	#page-mgmt input, input, input[type=text], #page-mgmt select, span.label { width: 90%; }
	.hiddenMobile { display: inherit;}

}

</style>
<div>
	<?php if ($page->errorCount()) { ?>
	    <div class="form_error"><?=$page->errorString()?></div>
	<?php } ?>
	<?php if ($page->success) { ?>
	    <div class="form_success"><?=$page->success?></div>
	<?php } ?>
</div>
<div class="flex-space-between">
<h2 style="display: inline-block;"><i class='fa fa-check-square' aria-hidden='true'></i>Support Tickets</h2>
<a class="button" href="/_support/request" style="margin-left: 30px;">Create new request</a>
	</div>
<div>
	<form id="pageForm" name="filterForm" method="get" action="/_support/tickets"  autocomplete="off">
	    <input type="hidden" name="filtered" value="<?=$_REQUEST['filtered']?>" />	    
	    <input id="sort_by" type="hidden" name="sort_by" value="" />
	    <input id="sort_direction" type="hidden" name="sort_direction" value="<?=($_REQUEST['sort_direction'] == 'desc') ? 'asc': 'desc';?>" />
	    <input id="min_date" type="hidden" name="min_date" readonly value="<?=$_REQUEST['min_date']?>" />
	    <div class="forms-filter">
	    <div class="flex-space-between">
			<h3>Filter Results</h3>
		</div>
		<div class="flex-space-between form-basis">
        <div>
	        <span class="label"><i class="fa fa-barcode" aria-hidden="true"></i> Serial #</span>
	        <input type="text" id="serial_number" name="serial_number" class="value input collectionField" value="<?=$selectedSerialNumber?>" onchange="updateReport()" />
        </div>
        <div>
	        <span class="label"><i class="fa fa-cog" aria-hidden="true"></i> Product:</span>
	        <select id="product_id" name="product_id" class="value input collectionField" onchange="updateReport()">
    	         <option value="ALL"<?php	if ($product == $selectedProduct) print " selected"; ?>>Choose a product</option>
                <?php foreach ($products as $product) { ?>
		            <option value="<?=$product->id?>"<?php	if ($product->id == $selectedProduct) print " selected"; ?>><?=$product->code?></option>
                <?php } ?>
	        </select>
	    </div>
        <div>
            <span class="label"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> After Date: <?=!empty($_REQUEST['min_date']) ? '[' . $_REQUEST['min_date']. ']' : '';?></span>
            <input type="text" id="datepicker" value="<?=$minDate?>">
        </div>
		<div class="marginBottom2rem"><a class="button btn-secondary" href="/_support/request_items" class="black">Clear Filters</a>
		</div>
		</div><!--end row-->
        
	    <div style="clear: both;"></div>
	    
	    <span class="label"><i class="fa fa-filter" aria-hidden="true"></i> Status</span>
	    <div class="flex-space-between form-basis">
		    <div><input type="checkbox" name="status_new" value="1" onclick="updateReport()"<?php if ($_REQUEST['status_new']) print " checked";?> />
		    <span class="value">New</span></div>
		    <div><input type="checkbox" name="status_active" value="1" onclick="updateReport()"<?php	if ($_REQUEST['status_active']) print " checked";?> />
		    <span class="value">Active</span></div>
		    <div><input type="checkbox" name="status_pending_customer" value="1" onclick="updateReport()"<?php if ($_REQUEST['status_pending_customer']) print " checked";?> />
		    <span class="value">Pending Customer</span></div>
		    <div><input type="checkbox" name="status_pending_vendor" value="1" onclick="updateReport()"<?php	if ($_REQUEST['status_pending_vendor']) print " checked";?> />
		    <span class="value">Pending Vendor</span></div>
		    <div><input type="checkbox" name="status_complete" value="1" onclick="updateReport()"<?php if ($_REQUEST['status_complete']) print " checked";?> />
		    <span class="value">Complete</span></div>
		    <div><input type="checkbox" name="status_closed" value="1" onclick="updateReport()"<?php	if ($_REQUEST['status_closed']) print " checked";?> />
		    <span class="value">Closed</span></div>
	    </div>
	</form>
</div>
<!--	Start First Row-->
<div class="tableBody">
	<div class="tableRowHeader">
		<div id="ticket-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'ticket'; updateReport()">Ticket #</div>
		<div id="date-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'requested'; updateReport()">Date Requested</div>
		<div id="requestor-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'requestor'; updateReport()">Requestor</div>
		<div id="product-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'product'; updateReport()">Product</div>
		<div id="serial-sortable-column" class="tableCell sortableHeader hiddenMobile" onclick="document.getElementById('sort_by').value = 'serial'; updateReport()">Serial #</div>
		<div id="status-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'status'; updateReport()">Status</div>
	</div> <!-- end row header -->
    <?php	foreach ($items as $item) { ?>
        <div class="tableRow">
	        <div class="tableCell"><span class="value"><a href="/_support/ticket/<?=$item->id?>"><?=$item->ticketNumber()?></a></span></div>
			<div class="tableCell"><span class="value"><?=$item->request->date_request?></span></div>
	        <div class="tableCell"><span class="value"><?=$item->request->customer->full_name()?></span></div>
	        <div class="tableCell"><span class="value"><?=$item->product->code?></span></div>
	        <div class="tableCell hiddenMobile"><span class="value"><?=$item->serial_number?></span></div>
	        <div class="tableCell"><span class="value"><?=ucwords(strtolower($item->status))?></span></div>
        </div>
    <?php	} ?>
</div>
