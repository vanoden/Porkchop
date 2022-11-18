<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="/js/monitor.js"></script>
<script type="text/javascript">
  
 // check reseller toggle
 $(document).ready(function() {

     $("#has_item_checkbox").change(function() {
         $("#product_details").toggle();
     });

    $("form select").each(function() {
        $(this).change(function () {
            checkEmptyValues();
        });
    }); 
 });

 function checkEmptyValues() {
  
  // get elements
  var btn_additem = document.getElementById('btn_additem');
  var btn_submit = document.getElementById('btn_submit');
  var completeFormMessage = document.getElementById('completeFormMessage');  

  btn_additem.removeAttribute('disabled');
  btn_submit.removeAttribute('disabled');
  completeFormMessage.style.display = 'none';
  
  $("form select").each(function(){
    if (!this.value || this.value == '') {
      btn_additem.setAttribute('disabled', 'disabled');
      btn_submit.setAttribute('disabled', 'disabled');
      completeFormMessage.style.display = 'block';
    }
  });
  $("input").each(function(){
    if (this.value == '') {
      btn_additem.setAttribute('disabled', 'disabled');
      btn_submit.setAttribute('disabled', 'disabled');
      completeFormMessage.style.display = 'block';
    }
  });
 }

 function checkProduct(lineNumber) {
  var productElem = document.getElementById('product_id' + lineNumber);
  var serialNumber = document.getElementById('serial_number' + lineNumber);
  var serialMessage = document.getElementById('serial_number_message' + lineNumber);
  serialNumber.focus();
  serialNumber.style.border = '1px solid gray';
  if (productElem.selectedIndex > 0) {
    if (document.getElementById('serial_number' + lineNumber).value.length > 0) serialMessage.display = 'none';
    return true;
  } else {
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
    $.get('/_register/api?method=checkLoginNotTaken&login=' + loginField.val(), function(data, status) {
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

  var productInput = document.getElementById('product_id' + lineNumber);
  checkProduct(lineNumber);
  
  var productID = productInput.options[productInput.selectedIndex].value;
  var serialInput = document.getElementById('serial_number' + lineNumber);
  var serialNumberMessage = document.getElementById('serial_number_message' + lineNumber);
  var serialNumberMessageOK = document.getElementById('serial_number_message_ok' + lineNumber);

  if (serialInput.value.length < 1) return true;
  var code = serialInput.value;
  var asset = Object.create(Asset);
  checkEmptyValues();
     
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

var line = 0;
   
function setVisibility(elementId, visibility) {
  document.getElementById(elementId).style.display = visibility;
}

function selectedType(elem) {
  if (document.getElementById('problem_type').value == "gas monitor") {
    setVisibility('generic_form', 'none');
    setVisibility('device_table', 'block');
    setVisibility('products_footer', 'block');
    setVisibility('issues_footer', 'none');
    setVisibility('btn_additem', 'inline');
  } else {
    setVisibility('device_table', 'none');
    setVisibility('generic_form', 'block');
    setVisibility('products_footer', 'none');
    setVisibility('issues_footer', 'block');
    setVisibility('btn_additem', 'none');
  }
  
  if (document.getElementById('problem_type').value == '') {
    setVisibility('generic_form', 'none');
    setVisibility('products_footer', 'none');	
    setVisibility('issues_footer', 'none');    
  }
}
   
function dropRow(line) {
    row = document.getElementById('row'+line);
    row.parentNode.removeChild(row);
}
   
function addRow() {
   
  line ++;
  var row = document.createElement('div');
  row.classList.add('tableRow');
  row.id = 'row'+line;
  document.getElementById('device_table').appendChild(row);

  /* Add Product Pulldown to new row */
  var productCell1 = document.createElement('div');
  productCell1.classList.add('tableCell');
  row.appendChild(productCell1);
  var productSelect = document.createElement('select');
  productSelect.name = 'product_id['+line+']';
  productSelect.id = 'product_id'+line;
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
  productSelect.addEventListener("change", function() { checkEmptyValues() }, false);

  /* Add Serial Input to new row */
  var productCell2 = document.createElement('div');
  productCell2.classList.add('tableCell');
  row.appendChild(productCell2);
  var serialNumber = document.createElement('input');
  serialNumber.name = "serial_number["+line+"]";
  serialNumber.type = "text";
  serialNumber.id = 'serial_number'+line;
  productCell2.appendChild(serialNumber);

  /* Add Problem Input to new row */
  var productCell3 = document.createElement('div');
  productCell3.classList.add('tableCell');
  row.appendChild(productCell3);
  var problem = document.createElement('input');
  problem.name = "line_description["+line+"]";
  problem.type = "text";
  problem.id = 'line_description'+line;
  problem.addEventListener('change', checkEmptyValues);
  productCell3.appendChild(problem);
  
  /* Add Delete Item to new row */
  var productCell4 = document.createElement('div');
  productCell4.classList.add('tableCell');
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
  
  $("#btn_additem").attr('disabled','disabled');
  $("#btn_submit").attr('disabled','disabled');
  $("#completeFormMessage").show();
        
}
</script>

<h2>New Support Request</h2>
<nav id="breadcrumb">
	<ul>
		<li><a href="/_support/tickets">Support Tickets</a></li>
		<li>New Support Request</li>
	</ul>
</nav>

<!-- SHould this have a breadcrumb and should it be linkable to Support -> add new -->
<?php	if ($page->errorCount() > 0) { ?>
  <section>
    <ul class="connectBorder errorText">
      <li><?=$page->errorString()?></li>
    </ul>
  <section>
<?php	}
  if (empty($GLOBALS['_SESSION_']->customer->organization->id)) return;
  if ($page->success) { ?>
    <section>
      <ul class="connectBorder progressText">
        <li><?=$page->success?></li>
      </ul>
    <section>
<?php	} else { ?>

<form id="supportRequest" name="supportRequest" method="post" action="/_support/request">
<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
  <section id="form-message">
    <ul class="connectBorder infoText">
      <li>Select the type of request and clearly define your problem. Make sure to list relevant Serial Numbers for any devices referenced.</li>
    </ul>
  </section>

  <section class="form-group">

    <ul class="form-grid four-col">
      <li>
        <label for="problemType">Type of Request</label>
        <select name="type" id="problem_type" class="value input" onchange="selectedType(this);">
          <option value="">Select</option>
          <option value="gas monitor">Gas Monitor</option>
          <option value="billing">Billing and Account</option>
          <option value="web portal">Web Portal</option>
        </select>
      </li>
    </ul>

    <section class="container" id="generic_form">
      <ul>
        <li>
          <label for="description">Describe Problem</label>
          <textarea name="description"></textarea>
        </li>
      </ul>
    </section>

    <section>
      <div class="tableBody bandedRows" id="device_table">
        <div class="tableRowHeader">
          <div class="tableCell">Product</div>
          <div class="tableCell">Serial #</div>
          <div class="tableCell">Problem</div>
          <div class="tableCell"></div>
        </div>
        <div class="tableRow">
          <div class="tableCell">
            <select id="product_id0" name="product_id[0]" class="value input">
              <option value="">None</option>
              <?php	foreach ($products as $product) { ?>
              <option value="<?=$product->id?>"><?=$product->code?></option>
              <?php	} ?>
            </select>
          </div>
          <div class="tableCell">
            <input id="serial_number0" type="text" name="serial_number[0]" class="value" onfocus="checkProduct(0);" onchange="checkSerial(0)" maxlength="50"/>
            <div id="serial_number_message0" style="color:red; display:none;">Serial number not found in our system</div>
            <div id="serial_number_message_ok0" style="color:green; display:none;">Serial number has been found, thank you for providing!</div>
          </div>
          <div class="tableCell">
            <input type="text" name="line_description[0]" class="value" onchange="checkEmptyValues(0)" />  
          </div>
          <div class="tableCell">
            <input type="button" name="btn_drop" class="value" value="Delete" onclick="dropRow(0);" />
          </div>
        </div>
      </div>
    </section>
 
    <section id="products_footer">
      <ul class="connectBorder infoText">
      <li id="completeFormMessage">Add all products you need support on. If you have it, add the serial number to expedite your service.</li>
      </ul>
      <ul class="button-bar">
      <li><input id="btn_additem" type="button" name="additem" class="button" value="Add Product" style="display: none" onclick="addRow();" />
      <input id="btn_submit" type="submit" name="btn_submit" class="button" value="Submit" /></li>
      </ul>
    </section>
 
    <section id="issues_footer">
      <ul class="connectBorder infoText">
      <li id="completeFormMessage">Please enter complete details about your issue so we can resolve it quickly.</li>
      </ul>
      <ul class="button-bar">
      <li><input id="btn_submit" type="submit" name="btn_submit" class="button" value="Submit" /></li>
      </ul>
    </section>

  </section>
</form>

<script>
   setVisibility('device_table', 'none');
   setVisibility('generic_form', 'none');
   setVisibility('products_footer', 'none');
   setVisibility('issues_footer', 'none');
</script>

<?php } ?>
