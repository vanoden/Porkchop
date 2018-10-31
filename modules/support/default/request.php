<style>
	div.table {
		display: none;
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
		border-bottom: 1px solid #dedede;
	}
</style>
<script language= "Javascript">
	var line = 0;
	function selectedType(elem) {
		if (document.getElementById('problem_type').value == "gas monitor") {
			document.getElementById('generic_form').style.display = 'none';
			document.getElementById('device_table').style.display = 'block';
			document.getElementById('btn_additem').style.display = 'inline';
		}
		else {
			document.getElementById('device_table').style.display = 'none';
			document.getElementById('generic_form').style.display = "block";
			document.getElementById('btn_additem').style.display = 'none';
		}
	}
	function dropRow(line) {
		row = document.getElementById('row'+line);
		row.parentNode.removeChild(row);
	}
	function addRow() {
		line ++;
		var row = document.createElement('div');
		row.classList.add('table_row');
		row.id = 'row'+line;
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
		addBtn.value = '-';
		productCell4.appendChild(addBtn);
		var lineID = line;
		addBtn.addEventListener("click", function(){
			dropRow(lineID);
		});
	}
</script>
<h2>Request Support</h2>
<?	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	}
	if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?	}
	else { ?>
<form name="supportRequest" method="post" action="/_support/request">
<div class="form_instruction">
	Select your problem type from the list.  Then clearly define your problem.  Make sure to list relevant Serial Numbers for any devices referenced.
</div>
<div class="container_narrow">
	<div class="label">Problem Type</div>
	<select name="type" id="problem_type" class="value input" onchange="selectedType(this);">
		<option value="">Select</option>
		<option value="gas monitor">Gas Monitor</option>
		<option value="billing">Billing and Account</option>
		<option value="web portal">Web Portal</option>
	</select>
</div>
<div class="container" id="generic_form">
	<div class="label">Describe Problem</div>
	<textarea name="description" class="value input"></textarea>
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
	<div class="table_row" id="row0">
		<div class="table_cell" style="width: 100px;">
			<select name="product_id[0]" class="value input" style="width: 150px; margin-right: 25px;">
				<option value="">None</option>
<?	foreach ($products as $product) { ?>
				<option value="<?=$product->id?>"><?=$product->code?></option>
<?	} ?>
			</select>
		</div>
		<div class="table_cell" style="width: 100px;">
			<input type="text" name="serial_number[0]" class="value input" style="width: 150px; margin-right: 25px;" />
		</div>
		<div class="table_cell" style="width: 500px;">
			<input type="text" name="line_description[0]" class="value input" style="width:400px; margin-right: 25px;" />
		</div>
		<div class="table_cell" style="width: 50px;">
			<input type="button" name="btn_drop" class="value input" style="width:50px" value="-" onclick="dropRow(0);" />
		</div>
	</div>
</div>
<div class="form_footer" colspan="2" style="text-align: center">
	<input id="btn_additem" type="button" name="additem" class="button" value="Another Product" style="display: none" onclick="addRow();" />
	<input type="submit" name="btn_submit" class="button" value="Submit" />
</div>
</form>
<?	} ?>
