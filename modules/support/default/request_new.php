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
	function selectedType(elem) {
		if (document.getElementById('problem_type').value == "gas monitor") {
			document.getElementById('generic_form').style.display = 'none';
			document.getElementById('device_table').style.display = 'inline-table';
		}
		else {
			document.getElementById('device_table').style.display = 'none';
			document.getElementById('generic_form').style.display = "inline-table";
		}
	}
	function addRow() {
		line ++;
		var row = document.createElement('div');
		row.classList.add('tableRow');
		document.getElementById('device_table').appendChild(row);

		var productCell1 = document.createElement('div');
		productCell1.classList.add('tableCell');
		row.appendChild(productCell1);
		var productSelect = document.createElement('select');
		productSelect.name = 'product_id['+line+']';
		productSelect.classList.add('value');
		productSelect.classList.add('wide_100per');
		productCell1.appendChild(productSelect);
		var productOpt0 = document.createElement('option');
		productOpt0.value = '';
		productOpt0.innerHTML = 'None';
		productSelect.appendChild(productOpt0);
<?	foreach ($products as $product) { ?>
		var productOpt<?=$product->id?> = document.createElement('option');
		productOpt<?=$product->id?>.value = '<?=$product->id?>';
		productOpt<?=$product->id?>.innerHTML = '<?=$product->code?>';
		productSelect.appendChild(productOpt<?=$product->id?>);
<?	} ?>
		productCell1.appendChild(productSelect);


		var productCell2 = document.createElement('div');
		productCell2.classList.add('tableCell');
		row.appendChild(productCell2);
		var serialNumber = document.createElement('input');
		serialNumber.name = "serial_number["+line+"]";
		serialNumber.classList.add('value');
		serialNumber.classList.add('wide_100per');
		productCell2.appendChild(serialNumber);

		var productCell3 = document.createElement('div');
		productCell3.classList.add('tableCell');
		row.appendChild(productCell3);
		var problem = document.createElement('input');
		problem.name = "line_description["+line+"]";
		problem.classList.add('value');
		problem.classList.add('wide_100per');
		productCell3.appendChild(problem);

		var productCell4 = document.createElement('div');
		productCell4.classList.add('tableCell');
		row.appendChild(productCell4);
		var addBtn = document.createElement('input');
		addBtn.type = 'button';
		addBtn.value = '+';
		productCell4.appendChild(addBtn);
	}
</script>

</script>
<div class="breadcrumbs">
	<a href="/_support/requests">Support</a>
	<a href="/_support/requests">Requests</a> > New Request
</div>

<!-- Error Messaging -->
<?	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	} ?>

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
		<div class="tableCell">
			<select name="organization_id" class="value wide_100per" onchange="populateCustomers();">
				<option value="">Select</option>
				<?	foreach ($organizations as $organization) { ?>
				<option value="<?=$organization->id?>"<? if ($organization->id == $_REQUEST['organization_id']) print " selected"; ?>><?=$organization->name?></option>
				<?	} ?>
			</select>
		</div>
		<div class="tableCell">
			<select class="value wide_100per" name="requestor_id">
				<option value="">Select</option>
				<?	foreach ($customers as $customer) { ?>
				<option value="<?=$customer->id?>"<? if ($customer->id == $_REQUEST['customer_id']) print " selected"; ?>><?=$customer->full_name()?></option>
				<?	} ?>
			</select>
		</div>
		<div class="tableCell">
			<select name="type" id="problem_type" class="value wide_100per" onchange="selectedType(this);">
				<option value="">Select</option>
				<option value="gas monitor">Gas Monitor</option>
				<option value="billing">Billing and Account</option>
				<option value="web portal">Web Portal</option>
			</select>
		</div>
		<div class="tableCell">
			<select class="value wide_100per" name="status">
				<option value="NEW"<? if ($_REQUEST['status'] == 'NEW') print " selected"; ?>>New</option>
				<option value="OPEN"<? if ($_REQUEST['status'] == 'OPEN') print " selected"; ?>>Open</option>
				<option value="CLOSED"<? if ($_REQUEST['status'] == 'CLOSED') print " selected"; ?>>Closed</option>
			</select>
		</div>
	</div>
</div>
<!--	END First Table -->		

		
<!--	START First Table -->
<div id="generic_form" class="tableBody half min-tablet marginTop_20" style="display:none;">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 100%;">Describe Problem</div>
	</div>
	<div class="tableRow">
		<div class="tableCell">
			<textarea name="description" class="value wide_100per"></textarea>
		</div>
	</div>
</div>
<!--	END First Table -->
		
		
<!--	START Last Table -->
<div id="device_table" class="tableBody min-tablet marginTop_20" style="display:none;">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 20%;">Product</div>
		<div class="tableCell" style="width: 20%;">Serial Number</div>
		<div class="tableCell" style="width: 50%;">Problem</div>
		<div class="tableCell" style="width: 10%;"></div>
	</div>
	<div class="tableRow">
		<div class="tableCell">
			<select name="product_id[0]" class="value wide_100per">
				<option value="">None</option>
				<?	foreach ($products as $product) { ?>
				<option value="<?=$product->id?>"><?=$product->code?></option>
				<?	} ?>
			</select>
		</div>
		<div class="tableCell">
			<input type="text" name="serial_number[0]" class="value wide_100per" />
		</div>
		<div class="tableCell">
			<input type="text" name="line_description[0]" class="value wide_100per" />
		</div>
		<div class="tableCell">
			<input type="button" name="additem[0]" class="value" value="+" onclick="addRow();" />
		</div>
	</div>
</div>
<!--	END Last Table -->	
	<div class="button-bar min-tablet marginTop_20">
		<input type="submit" class="button" name="btn_submit" value="Add Request" />
	</div>
	</form>
</div>
