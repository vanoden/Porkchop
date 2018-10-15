<style>
	div.table {
		display: table;
	}
	div.table_header {
		display: table-row;
		font-weight: bold;
	}
	div.table_row {
		display: table-row;
	}
	div.table_cell {
		display: table-cell;
		border-bottom: 1px solid blue;
	}
</style>
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
			document.getElementById('device_table').style.display = 'block';
		}
		else {
			document.getElementById('device_table').style.display = 'none';
			document.getElementById('generic_form').style.display = "block";
		}
	}
	function addRow() {
		line ++;
		var row = document.createElement('div');
		row.classList.add('table_row');
		document.getElementById('device_table').appendChild(row);

		var productCell1 = document.createElement('div');
		productCell1.classList.add('table_cell');
		row.appendChild(productCell1);
		var productSelect = document.createElement('select');
		productSelect.name = 'product_id['+line+']';
		productSelect.classList.add('value');
		productSelect.classList.add('input');
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
		productCell2.classList.add('table_cell');
		row.appendChild(productCell2);
		var serialNumber = document.createElement('input');
		serialNumber.name = "serial_number["+line+"]";
		serialNumber.classList.add('value');
		serialNumber.classList.add('input');
		productCell2.appendChild(serialNumber);

		var productCell3 = document.createElement('div');
		productCell3.classList.add('table_cell');
		row.appendChild(productCell3);
		var problem = document.createElement('input');
		problem.name = "line_description["+line+"]";
		problem.classList.add('value');
		problem.classList.add('input');
		productCell3.appendChild(problem);

		var productCell4 = document.createElement('div');
		productCell4.classList.add('table_cell');
		row.appendChild(productCell4);
		var addBtn = document.createElement('input');
		addBtn.type = 'button';
		addBtn.value = '+';
		productCell4.appendChild(addBtn);
	}
</script>
</script>
<div class="breadcrumbs"><a href="/_support/requests">Support</a><a href="/_support/requests">Requests</a></div>
<?	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	} ?>
<div style="width: 756px;">
	<form name="requestForm" method="post">
	<input type="hidden" name="request_id" value="<?=$request->id?>" />
	<h2>Request <?=$request->code?></h2>
	<div class="container_narrow">
		<span class="label">Organization</span>
		<select name="organization_id" class="value input" onchange="populateCustomers();">
			<option value="">Select</option>
	<?	foreach ($organizations as $organization) { ?>
			<option value="<?=$organization->id?>"<? if ($organization->id == $_REQUEST['organization_id']) print " selected"; ?>><?=$organization->name?></option>
	<?	} ?>
		</select>
	</div>
	<div class="container_narrow">
		<span class="label">Requested By</span>
		<select class="value" name="requestor_id">
			<option value="">Select</option>
	<?	foreach ($customers as $customer) { ?>
			<option value="<?=$customer->id?>"<? if ($customer->id == $_REQUEST['customer_id']) print " selected"; ?>><?=$customer->full_name()?></option>
	<?	} ?>
		</select>
	</div>
	<div class="container_narrow">
		<span class="label">Type</span>
		<select name="type" id="problem_type" class="value input" onchange="selectedType(this);">
			<option value="">Select</option>
			<option value="gas monitor">Gas Monitor</option>
			<option value="billing">Billing and Account</option>
			<option value="web portal">Web Portal</option>
		</select>
	</div>
	<div class="container_narrow">
		<span class="label">Status</span>
		<select class="value input" name="status">
			<option value="NEW"<? if ($_REQUEST['status'] == 'NEW') print " selected"; ?>>New</option>
			<option value="OPEN"<? if ($_REQUEST['status'] == 'OPEN') print " selected"; ?>>Open</option>
			<option value="CLOSED"<? if ($_REQUEST['status'] == 'CLOSED') print " selected"; ?>>Closed</option>
		</select>
	</div>
	<div class="container" id="generic_form">
		<div class="label">Describe Problem</div>
		<textarea name="description" class="value input" style="width: 100%"></textarea>
	</div>
	<div class="container" id="equipment_form">
	<div class="table" id="device_table">
		<div class="table_header">
			<div class="table_cell">
				Product
			</div>
			<div class="table_cell">
				Serial Number
			</div>
			<div class="table_cell">
				Problem
			</div>
			<div class="table_cell">
				&nbsp;
			</div>
		</div>
		<div class="table_row">
			<div class="table_cell" style="width: 100px;">
				<select name="product_id[0]" class="value input">
					<option value="">None</option>
	<?	foreach ($products as $product) { ?>
					<option value="<?=$product->id?>"><?=$product->code?></option>
	<?	} ?>
				</select>
			</div>
			<div class="table_cell" style="width: 100px;">
				<input type="text" name="serial_number[0]" class="value input" style="width: 50px;" />
			</div>
			<div class="table_cell" style="width: 500px;">
				<input type="text" name="line_description[0]" class="value input" style="width:500px" />
			</div>
			<div class="table_cell" style="width: 50px;">
				<input type="button" name="additem[0]" class="value input" style="width:50px" value="+" onclick="addRow();" />
			</div>
		</div>
	</div>
	<div class="form_footer">
		<input type="submit" class="button" name="btn_submit" value="Add Request" />
	</div>
	</form>
</div>