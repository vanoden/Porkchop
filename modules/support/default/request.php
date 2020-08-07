<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="/js/monitor.js"></script>
<script type="text/javascript">

    // validate password and submit if ok to go
    function submitForm() {
        if (document.register.password.value.length < 6) {
	        alert("Your password is too short.");
	        return false;
        }
        if (document.register.password.value != document.register.password_2.value) {
	        alert("Your passwords don't match.");
	        return false;
        }
        return true;
    }
   
   // check reseller toggle
   $(document).ready(function() {
       $("#has_item_checkbox").change(function() {
      	    $("#product_details").toggle();
       });
   });
   
	function checkProduct(lineNumber) {	
		var productElem = document.getElementById('product_id'+lineNumber);
		var serialNumber = document.getElementById('serial_number'+lineNumber);
		var serialMessage = document.getElementById('serial_number_message'+lineNumber);
		serialNumber.focus();
		serialNumber.style.border = '1px solid gray';
		if (productElem.selectedIndex > 0) {
			if (document.getElementById('serial_number'+lineNumber).value.length > 0) serialMessage.display = 'none';
			return true;
		}
		else {
			serialMessage.innerHTML = 'Select a product first';
			serialMessage.style.display = 'block';
			productElem.focus();
			return false;
		}
	}
	
	// make sure that the user name isn't taken
	function checkUserName() {
	    var loginField = $("#login");
	    var loginMessage = $("#login-message");
        $.get('/_register/api?method=checkLoginNotTaken&login=' + loginField.val(), function(data, status){
            if (data == 1) {
                loginField.css('border', '2px solid green');
                loginMessage.html('login is available');
                loginMessage.css('color', 'green');
            } else {
                loginField.css('border', '2px solid red');
                loginMessage.html('login is not available');
                loginMessage.css('color', 'red');
            }
        });
	}

	// make sure the serial number is valid
	function checkSerial(lineNumber) {
	
		var productInput = document.getElementById('product_id'+lineNumber);
		checkProduct(lineNumber);
		var productID = productInput.options[productInput.selectedIndex].value;

		var serialInput = document.getElementById('serial_number'+lineNumber);
		var serialNumberMessage = document.getElementById('serial_number_message'+lineNumber);
		var serialNumberMessageOK = document.getElementById('serial_number_message_ok'+lineNumber);

		if (serialInput.value.length < 1) return true;
		var code = serialInput.value;
		var asset = Object.create(Asset);

		if (asset.get(code)) {
			if (asset.product.id == productID) {
				serialInput.style.border = 'solid 2px green';
				serialNumberMessage.style.display = 'none';
				serialNumberMessageOK.innerHTML = 'Serial number has been found, thank you for providing!';
				serialNumberMessageOK.style.display = 'block';
				return true;
			} else {
				serialInput.style.border = 'solid 2px red';
				serialNumberMessage.innerHTML = 'Product not found with that serial number';
				serialNumberMessage.style.display = 'block';
				serialNumberMessageOK.style.display = 'none';
				return false;
			}
		} else {
			serialInput.style.border = 'solid 2px red';
			serialNumberMessage.innerHTML = 'Serial number not found in our system';
			serialNumberMessage.style.display = 'block';
			serialNumberMessageOK.style.display = 'none';
			return false;
		}
	}
</script>
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
	
	#page-mgmt input[type="text"] {
	    min-width: 300px;
	    margin: 0px 25px 0px 25px;
	}
	
</style>
<script>
	var line = 0;
	
	function setVisibility(elementId, visibility) {
    	document.getElementById(elementId).style.display = visibility;
	}
	
	function selectedType(elem) {
	
		if (document.getElementById('problem_type').value == "gas monitor") {
    		setVisibility('generic_form', 'none');
    		setVisibility('device_table', 'block');
    		setVisibility('form_footer', 'block');
    		setVisibility('btn_additem', 'inline');
		} else {
    		setVisibility('device_table', 'none');
    		setVisibility('generic_form', 'block');
    		setVisibility('form_footer', 'block');
    		setVisibility('btn_additem', 'none');
		}
		
	    if (document.getElementById('problem_type').value == '') {
            setVisibility('generic_form', 'none');
            setVisibility('form_footer', 'none');	    
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
		productSelect.id = 'product_id'+line;
		productSelect.classList.add('value');
		productSelect.classList.add('input');
		productCell1.appendChild(productSelect);
		var productOpt0 = document.createElement('option');
		productOpt0.value = '';
		productOpt0.innerHTML = 'None';
		productSelect.appendChild(productOpt0);
<?php	foreach ($products as $product) { ?>
		var productOpt<?=$product->id?> = document.createElement('option');
		productOpt<?=$product->id?>.value = '<?=$product->id?>';
		productOpt<?=$product->id?>.innerHTML = '<?=$product->code?>';
		productSelect.appendChild(productOpt<?=$product->id?>);
<?php	} ?>
		productCell1.appendChild(productSelect);

		var productCell2 = document.createElement('div');
		productCell2.classList.add('table_cell');
		row.appendChild(productCell2);
		var serialNumber = document.createElement('input');
		serialNumber.name = "serial_number["+line+"]";
		serialNumber.id = 'serial_number'+line;
		serialNumber.classList.add('value');
		serialNumber.classList.add('input');
		serialNumber.setAttribute('style','min-width: 300px; margin: 0px 25px 0px 25px;');
		productCell2.appendChild(serialNumber);
		
		var productCell3 = document.createElement('div');
		productCell3.classList.add('table_cell');
		row.appendChild(productCell3);
		var problem = document.createElement('input');
		problem.name = "line_description["+line+"]";
		problem.id = 'line_description'+line;
		problem.classList.add('value');
		problem.classList.add('input');
        problem.setAttribute('style','min-width: 300px; margin: 0px 25px 0px 25px;');
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
		
		var serialNumberMessage = document.createElement('div');
		serialNumberMessage.id = 'serial_number_message'+line;
		serialNumberMessage.setAttribute('style','color:red; display:none;');
		productCell2.appendChild(serialNumberMessage);
		
		var serialNumberMessageOK = document.createElement('div');
		serialNumberMessageOK.id = 'serial_number_message_ok'+line;
		serialNumberMessageOK.setAttribute('style','color:green; display:none;');
		productCell2.appendChild(serialNumberMessageOK);
		
		newSerialNumber = document.getElementById('serial_number'+line);
		newSerialNumber.addEventListener("change", function() { checkSerial(lineID); }, false);
		newSerialNumber.addEventListener("focus", function() { checkProduct(lineID); }, false);
	}
</script>
<h2><i class='fa fa-phone' aria-hidden='true'></i> Request Support</h2>
<?php	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?php	}
	if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?php	} else { ?>
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
			<span class="label"><i class="fa fa-barcode" aria-hidden="true"></i> Serial #</span>
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
			<select id="product_id0" name="product_id[0]" class="value input">
				<option value="">None</option>
<?php	foreach ($products as $product) { ?>
				<option value="<?=$product->id?>"><?=$product->code?></option>
<?php	} ?>
			</select>
		</div>
		<div class="table_cell" style="width: 100px;">		
  		  <input id="serial_number0" type="text" name="serial_number[0]" class="value input" onfocus="checkProduct(0);" onchange="checkSerial(0)" maxlength="50"/>
          <div id="serial_number_message0" style="color:red; display:none;">Serial number not found in our system</div>
          <div id="serial_number_message_ok0" style="color:green; display:none;">Serial number has been found, thank you for providing!</div>			
		</div>
		<div class="table_cell" style="width: 500px;">
			<input type="text" name="line_description[0]" class="value input" />
		</div>
		<div class="table_cell" style="width: 50px;">
			<input type="button" name="btn_drop" class="value input" value="-" onclick="dropRow(0);" />
		</div>
	</div>
</div>
<div id="form_footer" class="form_footer" colspan="2" style="text-align: center">
	<input id="btn_additem" type="button" name="additem" class="button" value="Another Product" style="display: none" onclick="addRow();" />
	<input type="submit" name="btn_submit" class="button" value="Submit" />
</div>
</form>
<script>
    setVisibility('generic_form', 'none');
    setVisibility('form_footer', 'none');
</script>
<?php } ?>
