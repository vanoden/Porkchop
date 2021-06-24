<script language="Javascript">
	function goForm(selectedForm) {
		document.requestForm.action = '/_support/request_'+selectedForm;
		document.requestForm.submit();
	}
	function populateCustomers() {
		document.requestForm.action = '/_support/request_new';
		document.requestForm.btn_submit.value = 'Org Selected';
		document.requestForm.submit();
	}
	var line = 1;
</script>
<div class="breadcrumbs">
	<a href="/_support/requests">Support</a> &gt; New Request | <a href="/_support/request_items" class="button more" style="background-color:green;">Request Tickets</a>
</div>

<!-- Error Messaging -->
<?php	if ($page->errorCount()) { ?>
    <div class="form_error"><?=$page->errorString()?></div>
<?php	} ?>

<div>
	<form name="requestForm" method="post">
	        <input type="hidden" name="request_id" value="<?=$request->id?>" />
	        <h2>Request <?=$request->code?></h2>
	        
            <!--	START First Table -->
	        <div class="tableBody min-tablet">
	        <div class="tableRowHeader">
		        <div class="tableCell" style="width: 36%;">Organization</div>
		        <div class="tableCell" style="width: 30%;">Requested By</div>
		        <div class="tableCell" style="width: 24%;">Type</div>
		        <div class="tableCell" style="width: 10%;">Status</div>
	        </div>
	        <div class="tableRow">
		        <div class="tableCell"><?=$organization->name?></div>
		        <div class="tableCell">
			        <select class="value wide_100per" name="requestor_id">
				        <option value="">Select</option>
				        <?php	foreach ($customers as $customer) { ?>
				        <option value="<?=$customer->id?>" <?php if ($customer->id == $_REQUEST['customer_id']) print "selected"; ?>><?=$customer->full_name()?></option>
				        <?php	} ?>
			        </select>
		        </div>
		        <div class="tableCell">Gas Monitor</div>
		        <div class="tableCell">
			        <select class="value wide_100per" name="status">
				        <option value="NEW" <?php if ($_REQUEST['status'] == 'NEW') print "selected"; ?>>New</option>
				        <option value="OPEN" <?php if ($_REQUEST['status'] == 'OPEN') print "selected"; ?>>Open</option>
				        <option value="CLOSED" <?php if ($_REQUEST['status'] == 'CLOSED') print "selected"; ?>>Closed</option>
			        </select>
		        </div>
	        </div>
	        <!--	END First Table -->
        </div>

        <!--	START Last Table -->
        <div id="device_table" class="tableBody min-tablet marginTop_20">
	        <div class="tableRowHeader">
		        <div class="tableCell" style="width: 20%;">Product</div>
		        <div class="tableCell" style="width: 20%;">Serial Number</div>
		        <div class="tableCell" style="width: 60%;">Problem</div>
	        </div>
	        <div class="tableRow">
		        <div class="tableCell">
			        <input type="hidden" name="product_id" value="<?=$product->id?>"/>
			        <?=$product->code?>
		        </div>
		        <div class="tableCell">
			        <input type="hidden" name="serial_number" value="<?=$asset->code?>"/>
			        <?=$asset->code?>
		        </div>
		        <div class="tableCell">
			        <input type="text" name="line_description" class="value wide_100per" />
		        </div>
	        </div>
        </div>
        <!--	END Last Table -->	

        <div class="button-bar min-tablet marginTop_20">
	        <input type="submit" class="button" name="btn_submit" value="Add Request" />
        </div>
	</form>
</div>
