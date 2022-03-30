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

		// Hide and show the filters area
		function hideFilters() {
		var x = document.getElementById("filters");
		const element =  document.querySelector(".expanding");
		if (x.style.display === "none") {
			x.style.display = "block";
			element.style.backgroundColor = "#7d7d7d";
		} else {
			x.style.display = "none";
			element.style.backgroundColor = "#4c9dea";
		}
		}

</script>
<style>
body {font-size: 10px;}
section article.segment { width: 100%; }
.secondaryHeader { padding: 1rem; }
label { margin: 3px 6px 3px 0px; font-weight: 600; font-size: 0.8rem;}
form li { list-style-type: none; }
form ul { padding-left: 0; }

.table-flex { display: flex; }
.tableBody { max-width: none; border: none; }
.tableRow, .tableCell { display: block; width: 100%;}
.tableRow:nth-child(odd) {background: #eaf1f4; }
.tableRow { padding: 1rem .7rem; }
.tableCell:nth-child(1) * { font-size: 1.3rem; margin-top:1rem; font-weight: 400;}
span.value, .tableRowHeader > *, #page-mgmt input, input, input[type=text] { font-size: 1rem; line-height: 1.4rem; }
.tableRowHeader { display: none; }
#filters { display: none; }


.forms-filter {background: rgba(170,186,180,0.15); padding: 20px; }
a.button, button { margin: 0.2rem .3rem 0.8rem 0rem; background: #2FC61E; color: white; font-weight: 400; border: none; border-radius: 4px; padding: .4rem .6rem; font-size: .8rem;}
a.button:hover, button:hover { background: #21b910;}
a.button.btn-secondary { background: #8b8f8b; }
a.button.btn-secondary:hover { background: #686c68;}
#page-mgmt input, input, input[type=text], #page-mgmt select { padding: 0.1rem 0.4rem; background: rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.1); border-radius: 0; -khtml-border-radius: 0; box-shadow: none; margin: .25rem 0 0.8rem; font-size: 0.8rem; font-weight: 400; min-height: 1.6rem; width: 50%; -webkit-appearance: none; -moz-appearance: none;}
#page-mgmt input[type=checkbox] { -webkit-appearance: checkbox; width: auto; margin-bottom: 0.3rem; vertical-align: middle; }
.marginBottom2rem { margin-bottom: 2rem; }

input[type="text"], input[type=text], label { display: inline; }
.hiddenMobile { display: none;}
button.expanding::after { content: "\0020\25BE"; }
button.expanding { background-color: #7d7d7d; }

@media (min-width:600px) {
	body {font-size: 16px;}
	.flex-space-between {display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center;}
	.flex-space-between.form-basis > * {flex: 1 0 140px; }
	#page-mgmt input, input, input[type=text], #page-mgmt select, span.value, label { font-size: 0.8rem; width: 90%;}
	.table-flex { display: flex; }
	.tableRowHeader { display: table-header-group; }
	.tableBody { max-width: none; border: none; }
	.tableRow { display: table-row;}
	.tableCell { display: inline-block; width: 15%; padding: 0.3% 0.5%;}
	.tableCell:nth-child(1) * { font-size: 0.8rem; }
	.hiddenMobile { display: inherit;}
	.hiddenDesktop { display: none; }
	.checkboxRow { vertical-align: middle; }

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
<div class="secondaryHeader">
	<h1>Support Tickets</h1>
	<button class="expanding" onclick="hideFilters()">Filter Results</button>
	<button href="/_support/request">New Request</button>
</div>
<!--	Insert Filter Section -->
<div>
	<form id="pageForm" name="filterForm" method="get" action="/_support/tickets"  autocomplete="off">
		<input type="hidden" name="filtered" value="<?=$_REQUEST['filtered']?>" />	    
		<input id="sort_by" type="hidden" name="sort_by" value="" />
		<input id="sort_direction" type="hidden" name="sort_direction" value="<?=($_REQUEST['sort_direction'] == 'desc') ? 'asc': 'desc';?>" />
		<input id="min_date" type="hidden" name="min_date" readonly value="<?=$_REQUEST['min_date']?>" />
		<div id="filters" class="forms-filter">
			<div class="flex-space-between form-basis">
				<ul>
					<label for="serial_number"><i class="fa fa-barcode" aria-hidden="true"></i> Serial #</label>
					<input type="text" id="serial_number" name="serial_number" class="value input collectionField" value="<?=$selectedSerialNumber?>" onchange="updateReport()" />
				</ul>
				<ul>
					<label for="product_id">Product:</label>
					<select id="product_id" name="product_id" class="value input collectionField" onchange="updateReport()">
						<option value="ALL"<?php	if ($product == $selectedProduct) print " selected"; ?>>Choose a product</option>
						<?php foreach ($products as $product) { ?>
						<option value="<?=$product->id?>"<?php	if ($product->id == $selectedProduct) print " selected"; ?>><?=$product->code?></option>
						<?php } ?>
					</select>
				</ul>
				<ul>
							<label for="datepicker">After Date: <?=!empty($_REQUEST['min_date']) ? '[' . $_REQUEST['min_date']. ']' : '';?></label>
							<input type="date" id="datepicker" value="<?=$minDate?>">
						</ul>
				<ul style="margin-top: 0.5rem;"><a class="button btn-secondary" href="/_support/request_items" class="black">Clear Filters</a>
				</ul>
		</div>
	    
		<h4>Status</h4>
		<ul class="flex-space-between form-basis checkboxRow">
			<li>
				<input type="checkbox" name="status_new" value="1" onclick="updateReport()"<?php if ($_REQUEST['status_new']) print " checked";?> />
				<label for="status_new">New</label>
					</li>
			<li>
				<input type="checkbox" name="status_active" value="1" onclick="updateReport()"<?php	if ($_REQUEST['status_active']) print " checked";?> />
				<label for="status_active">Active</label>
					</li>
			<li>
				<input type="checkbox" name="status_pending_customer" value="1" onclick="updateReport()"<?php if ($_REQUEST['status_pending_customer']) print " checked";?> />
				<label for="status_pending_customer">Pending Customer</label>
					</li>
			<li>
				<input type="checkbox" name="status_pending_vendor" value="1" onclick="updateReport()"<?php	if ($_REQUEST['status_pending_vendor']) print " checked";?> />
				<label for="status_pending_vendor">Pending Vendor</label>
					</li>
			<li>
				<input type="checkbox" name="status_complete" value="1" onclick="updateReport()"<?php if ($_REQUEST['status_complete']) print " checked";?> />
				<label for="status_complete">Complete</label>
					</li>
			<li>
				<input type="checkbox" name="status_closed" value="1" onclick="updateReport()"<?php	if ($_REQUEST['status_closed']) print " checked";?> />
				<label for="status_closed">Closed</label>
			</li>
		</ul>
	</form>
</div>
<!--    End Filter Section -->

<!--	Start First Row-->
<div class="tableBody">
	<div class="tableRowHeader">
		<div id="ticket-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'ticket'; updateReport()">Ticket #</div>
		<div id="date-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'requested'; updateReport()">Date Requested</div>
		<div id="requestor-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'requestor'; updateReport()">Requestor</div>
		<div id="product-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'product'; updateReport()">Product</div>
		<div id="serial-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'serial'; updateReport()">Serial #</div>
		<div id="status-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'status'; updateReport()">Status</div>
	</div> <!-- end row header -->
    <?php	foreach ($items as $item) { ?>
        <div class="tableRow">
	        <div class="tableCell"><span class="hiddenDesktop value">Ticket #</span><span class="value"><a href="/_support/ticket/<?=$item->id?>"><?=$item->ticketNumber()?></a></span></div>
					<div class="tableCell"><span class="hiddenDesktop value">Date requested: </span><span class="value"><?=shortDate($item->request->date_request)?></span></div>
	        <div class="tableCell"><span class="hiddenDesktop value">Requested by: </span><span class="value"><?=$item->request->customer->initials()?></span></div>
	        <div class="tableCell"><span class="value"><span class="hiddenDesktop value">Product Name: </span><?=$item->product->code?></span></div>
	        <div class="tableCell"><span class="value"><span class="hiddenDesktop value">Serial #: </span><?=$item->serial_number?></span></div>
	        <div class="tableCell"><span class="value"><span class="hiddenDesktop value">Status: </span><?=ucwords(strtolower($item->status))?></span></div>
        </div>
    <?php	} ?>
</div>
<div>

