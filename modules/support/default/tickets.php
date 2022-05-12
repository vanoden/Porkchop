<script defer src="/js/dateSelect.js"></script>
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
		var filterForm = document.getElementById('filterForm');	
		filterForm.filtered.value = 1;
		filterForm.submit();
		return true;
	}

	function toggleFilters() {
		var filterForm = document.getElementById('filterForm');
		if (filterForm.style.display == "block") filterForm.style.display = "none";
		else (filterForm.style.display = "block");
	}

	function clearFilters() {
		document.getElementById('datepicker').removeEventListener("blur", pickerListener, false);
		document.getElementById('serial_number').value = '';
		document.getElementById('product_id').value = '';
		document.getElementById('datepicker').value = '';
		document.getElementById('min_date').value = '';
		filterForm.submit();
		return true;
	}
	
	// date picker with max date being current day
	window.addEventListener("load", () => {
		var dateSelect = Object.create(DateSelect);
		dateSelect.defaultDate = new Date();
		dateSelect.showFields = false;
		dateSelect.elem = document.getElementById('datepicker');
		dateSelect.showForm();
		document.getElementById('datepicker').addEventListener("blur", pickerListener, false);
	});

	function pickerListener() {
		document.getElementById('min_date').value = document.getElementById('datepicker').value;
		toggleFilters();
		updateReport();
	}

</script>
<div>
	<?php if ($page->errorCount()) { ?>
	    <div class="form_error"><?=$page->errorString()?></div>
	<?php } ?>
	<?php if ($page->success) { ?>
	    <div class="form_success"><?=$page->success?></div>
	<?php } ?>
</div>

<div class="secondaryHeader">
	<h2>Support Tickets</h2>
	<button class="expanding" onclick="toggleFilters()">Filter Results</button>
	<button onclick="window.location.href='/_support/request';">New Request</button>
</div>

<!--	Insert Filter Section -->
<div>
	<form id="filterForm" name="filterForm" method="get" action="/_support/tickets"  autocomplete="off" class="bg-shaded padded" style="display: none;">
		<input type="hidden" name="filtered" value="<?=$_REQUEST['filtered']?>" />	    
		<input id="sort_by" type="hidden" name="sort_by" value="" />
		<input id="sort_direction" type="hidden" name="sort_direction" value="<?=($_REQUEST['sort_direction'] == 'desc') ? 'asc': 'desc';?>" />
		<input id="min_date" type="hidden" name="min_date" readonly value="<?=$_REQUEST['min_date']?>" />
		<ul class="form-grid four-col">
			<li>
				<label for="serial_number"><i class="fa fa-barcode" aria-hidden="true"></i> Serial #</label>
				<input type="text" id="serial_number" name="serial_number" class="value input collectionField" value="<?=$selectedSerialNumber?>" onchange="updateReport()" />
			</li>
			<li>
				<label for="product_id">Product:</label>
				<select id="product_id" name="product_id" class="value input collectionField" onchange="updateReport()">
					<option value="ALL"<?php	if ($product == $selectedProduct) print " selected"; ?>>Choose a product</option>
					<?php foreach ($products as $product) { ?>
					<option value="<?=$product->id?>"<?php	if ($product->id == $selectedProduct) print " selected"; ?>><?=$product->code?></option>
					<?php } ?>
				</select>
			</li>
			<li>
				<label for="datepicker">After Date: <?=!empty($_REQUEST['min_date']) ? '[' . $_REQUEST['min_date']. ']' : '';?></label>
				<input type="date" id="datepicker" value="<?=$minDate?>">
			</li>
			<button class="iconButton closeIcon" onclick="clearFilters()">Clear Filters</button>
		</ul>
		<ul class="form-grid three-col">
			<h4>Status:</h4>
			<li class="form-selectors"><input type="checkbox" name="status_new" value="1" onclick="updateReport()"<?php if ($_REQUEST['status_new']) print " checked";?> />
				<label for="status_new">New</label>
			</li class="form-selectors">
			<li class="form-selectors"><input type="checkbox" name="status_active" value="1" onclick="updateReport()"<?php	if ($_REQUEST['status_active']) print " checked";?> /><label for="status_active">Active</label></li>
			<li class="form-selectors"><input type="checkbox" name="status_pending_customer" value="1" onclick="updateReport()"<?php if ($_REQUEST['status_pending_customer']) print " checked";?> />
				<label for="status_pending_customer">Pending Customer</label></li>
			<li class="form-selectors"><input type="checkbox" name="status_pending_vendor" value="1" onclick="updateReport()"<?php	if ($_REQUEST['status_pending_vendor']) print " checked";?> />
				<label for="status_pending_vendor">Pending Vendor</label></li>
			<li class="form-selectors"><input type="checkbox" name="status_complete" value="1" onclick="updateReport()"<?php if ($_REQUEST['status_complete']) print " checked";?> />
				<label for="status_complete">Complete</label></li>
			<li class="form-selectors"><input type="checkbox" name="status_closed" value="1" onclick="updateReport()"<?php	if ($_REQUEST['status_closed']) print " checked";?> />
				<label for="status_closed">Closed</label></li>
		</ul>
	</form>
</div>
<!--    End Filter Section -->

<!--	Start First Row-->
<div class="tableBody bandedRows">
	<div class="tableRowHeader">
		<div id="ticket-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'ticket'; updateReport()">Ticket #</div>
		<div id="date-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'requested'; updateReport()">Requested</div>
		<div id="requestor-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'requestor'; updateReport()">Requestor</div>
		<div id="product-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'product'; updateReport()">Product</div>
		<div id="serial-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'serial'; updateReport()">Serial #</div>
		<div id="status-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_by').value = 'status'; updateReport()">Status</div>
	</div> <!-- end row header -->
	<?php	foreach ($items as $item) { ?>
	<div class="tableRow">
		<div class="tableCell"><span class="hiddenDesktop value">Ticket #</span><span class="value"><a href="/_support/ticket/<?=$item->id?>"><?=$item->ticketNumber()?></a></span></div>
		<div class="tableCell"><span class="hiddenDesktop value">Requested: </span><span class="value"><?=shortDate($item->request->date_request)?></span></div>
		<div class="tableCell"><span class="hiddenDesktop value">Requested by: </span><span class="value avatar"><?=$item->request->customer->initials()?></span><span class="value"><?=$item->request->customer->full_name()?></span></div>
		<div class="tableCell"><span class="hiddenDesktop value">Product Name: </span><span class="value"><?=$item->product->code?></span></div>
		<div class="tableCell"><span class="hiddenDesktop value">Serial #: </span><span class="value"><?=$item->serial_number?></span></div>
		<div class="tableCell"><span class="hiddenDesktop value">Status: </span><span class="value"><?=ucwords(strtolower($item->status))?></span></div>
	</div>
	<?php	} ?>
	</div>
<div>
