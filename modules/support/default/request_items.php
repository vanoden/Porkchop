<script>
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
<div style="width: 756px;">
	<div class="breadcrumbs">
		<a href="/_support/requests">Support Home</a>
    	<a href="/_support/requests">Requests</a> &gt; Tickets
	</div>
	<?	if ($page->errorCount()) { ?>
	    <div class="form_error"><?=$page->errorString()?></div>
	<? } ?>
	<?	if ($page->success) { ?>
	    <div class="form_success"><?=$page->success?></div>
	<?	} ?>
</div>
<h2 style="display: inline-block;"><i class='fa fa-check-square' aria-hidden='true'></i> Request [Tickets]</h2>
<?php include(MODULES.'/support/partials/search_bar.php'); ?>

<div style="width: 756px;">
	<form id="pageForm" name="filterForm" method="get" action="/_support/request_items"  autocomplete="off">
	    <input type="hidden" name="filtered" value="<?=$_REQUEST['filtered']?>" />
	    <input id="min_date" type="hidden" name="min_date" readonly value="<?=$_REQUEST['min_date']?>" />
	    <div style="width: 100%; border: solid 1px #888a85; padding: 10px; margin: 10px; margin-left: 0px;">
	    <h3 style="padding: 0px; margin: 0px;">Filter Results</h3><br/>
        <div style="width: 25%; float:left;">
	        <span class="label"><i class="fa fa-barcode" aria-hidden="true"></i> Serial #</span>
	        <input type="text" id="serial_number" name="serial_number" class="value input collectionField" value="<?=$selectedSerialNumber?>" onchange="updateReport()" />
        </div>
        <div style="width: 42%; float:left;">
	        <span class="label"><i class="fa fa-cog" aria-hidden="true"></i> Product:</span>
	        <select id="product_id" name="product_id" class="value input collectionField" onchange="updateReport()">
    	         <option value="ALL"<? if ($product == $selectedProduct) print " selected"; ?>>ALL</option>
                <?	foreach ($products as $product) { ?>
		            <option value="<?=$product->id?>"<? if ($product->id == $selectedProduct) print " selected"; ?>><?=$product->code?></option>
                <?	} ?>
	        </select>
	    </div>
        <div style="width: 33%; float:left;">
            <span class="label"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> After Date: <?=!empty($_REQUEST['min_date']) ? '[' . $_REQUEST['min_date']. ']' : '';?></span>
            <input type="text" id="datepicker" value="<?=$minDate?>">
        </div>
        
	    <div style="clear: both;"></div>
	    
	    <span class="label"><i class="fa fa-filter" aria-hidden="true"></i> Status</span>
	    <div class="checkbox-row">
		    <input type="checkbox" name="status_new" value="1" onclick="updateReport()"<? if ($_REQUEST['status_new']) print " checked";?> />
		    <span class="value">NEW</span>
		    <input type="checkbox" name="status_active" value="1" onclick="updateReport()"<? if ($_REQUEST['status_active']) print " checked";?> />
		    <span class="value">ACTIVE</span>
		    <input type="checkbox" name="status_pending_customer" value="1" onclick="updateReport()"<? if ($_REQUEST['status_pending_customer']) print " checked";?> />
		    <span class="value">PENDING CUSTOMER</span>
		    <input type="checkbox" name="status_pending_vendor" value="1" onclick="updateReport()"<? if ($_REQUEST['status_pending_vendor']) print " checked";?> />
		    <span class="value">PENDING VENDOR</span>
		    <input type="checkbox" name="status_complete" value="1" onclick="updateReport()"<? if ($_REQUEST['status_complete']) print " checked";?> />
		    <span class="value">COMPLETE</span>
		    <input type="checkbox" name="status_closed" value="1" onclick="updateReport()"<? if ($_REQUEST['status_closed']) print " checked";?> />
		    <span class="value">CLOSED</span>
	    </div>
	    
	    <span style="float: right;"><a href="/_support/request_items" class="black"><i class="fa fa-ban" aria-hidden="true"></i> Clear Form</a></span>
	    <br/>
	</form>
</div>
<!--	Start First Row-->
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 9%;">Ticket #</div>
		<div class="tableCell" style="width: 17%;">Date Requested</div>
		<div class="tableCell" style="width: 20%;">Requestor</div>
		<div class="tableCell" style="width: 20%;">Organization</div>
		<div class="tableCell" style="width: 13%;">Product</div>
		<div class="tableCell" style="width: 12%;">Serial #</div>
		<div class="tableCell" style="width: 9%;">Status</div>
	</div> <!-- end row header -->
    <?	foreach ($items as $item) { ?>
	    <div class="tableRow">
		    <div class="tableCell">
			    <span class="value"><a href="/_support/request_item/<?=$item->id?>"><?=$item->ticketNumber()?></a></span>
		    </div>
		    <div class="tableCell">
			    <span class="value"><?=$item->request->date_request?></span>
		    </div>
		    <div class="tableCell">
			    <span class="value"><?=$item->request->customer->full_name()?></span>
		    </div>
		    <div class="tableCell">
			    <span class="value"><?=$item->request->customer->organization->name?></span>
		    </div>
		    <div class="tableCell">
			    <span class="value"><?=$item->product->code?></span>
		    </div>
		    <div class="tableCell">
			    <span class="value"><?=$item->serial_number?></span>
		    </div>
		    <div class="tableCell">
			    <span class="value"><?=ucwords(strtolower($item->status))?></span>
		    </div>
	    </div>
    <?	} ?>
</div>
