<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="/js/monitor.js"></script>
<script src="/js/geography.js"></script>
<script type="text/javascript">

// validate email from user
function validateEmail(email) {
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) return true;
    alert("Email: " + email + " is not valid")
    return false;
}

// validate password and submit if ok to go
function submitForm() {
  var emailField = $("#email");
  if (!validateEmail(emailField.val())) {
    console.log("email is not valid");
    // $(emailField).addClass("input-failed");
    return false;
  } else {
    console.log("email is good");
    // $(emailField).addClass("input-passed");     
  }

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

function checkProduct() {
    var productElem = document.getElementById('product_id');
    var serialNumber = document.getElementById('serial_number');
    var serialMessage = document.getElementById('serial_number_message');
    serialNumber.focus();
    serialNumber.style.border = '1px solid gray';
    if (productElem.selectedIndex > 0) {
        if (document.getElementById('serial_number').value.length > 0) serialMessage.display = 'none';
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

// Check password strength
function checkPasswordStrength() {
  var customer = document.createObject(Customer);
  var passwordField = document.getElementById('password');
  var passwordMessage = document.getElementByid('password-message');
  if (customer.checkPasswordStrength(myPasswordVar)) {
    passwordField.classList.add("input-passed");
    passwordMessage.innerHTML = 'strong password';
  }
  else {
    passwordField.classList.add("input-failed");
    passwordMessage.innerHTML = 'please use a stronger password';
  }
}

// make sure the serial number is valid
function checkSerial() {
  var productInput = document.getElementById('product_id');
  checkProduct();
  var productID = productInput.options[productInput.selectedIndex].value;
  var serialInput = document.getElementById('serial_number');
  var serialNumberMessage = document.getElementById('serial_number_message');
  var serialNumberMessageOK = document.getElementById('serial_number_message_ok');

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
    serialNumberMessage.innerHTML = 'Serial number not found in our system FUBAR';
    serialNumberMessage.style.display = 'block';
    serialNumberMessageOK.style.display = 'none';
    return false;
  }
}

function checkRegisterProduct(){
  console.log("checkRegisterProduct running");
  var checkBoxItem = document.getElementById("has_item_checkbox");
  var fieldsProductType = document.getElementById("product_type");
  var fieldsProductSerial = document.getElementById("product_serial");
  console.log(checkBoxItem.checked);
  if (checkBoxItem.checked == true) {
    fieldsProductType.style.display = "block";
    fieldsProductSerial.style.display = "block"; 
  } else {
    fieldsProductType.style.display = "none";
    fieldsProductSerial.style.display = "none"; 
  }
}

function getProvinces() {
	var countryElem = document.getElementById('country_id');
	var provinceElem = document.getElementById('province_id');

	var length = provinceElem.options.length;
	for (var i = length - 1; i >= 0; i--) {
		provinceElem.remove(i);
	}

	var country = Object.create(Country);
	console.log("Country ID: "+countryElem.value);
	country.id = countryElem.value;
	if (country.id > 0) {
		country.load();
		var provinces = country.getProvinces();
		for(var i = 0; i < provinces.length; i ++) {
			var option = document.createElement('option');
			option.value =  provinces[i].id;
			option.innerHTML = provinces[i].name;
			provinceElem.appendChild(option);
		}
		return true;
	}
	else {
		return false;
	}
}

</script>

<?php
  if (isset($page->isVerifedAccount)) {
    if ($page->isVerifedAccount) {
      ?>
        <section>
          <ul class="connectBorder progressText">
            <li>Your account has been verified.</li>
          </ul>
        <section>
        <section>
          <p>You may login to your account <a href="/_register/login">here</a></p>
          <i><strong>Note:</strong> Our account administrators will soon fully approve your account to use our platform.</i>
        <section>
      <?php        
    } else {
      ?>
        <h3>Account could not be verified</h3>
        <h5>Please check your <strong>spam / other</strong> mail folders in case you still need to find the correct verification link.</h5>
        <form name="register" action="/_register/new_customer" method="POST">
            <input type="hidden" name="method" value="resend">
            <input type="hidden" name="login" value="<?=$_REQUEST['login'];?>">
            <input type="submit" class="button" value="Resend Email" style="height: 35px; width: 190px;">
        </form>
      <?php
    }
  } else {
  ?>

<h2>New Customer Registration</h2>

<section id="form-message">
  <ul class="connectBorder infoText">
    <li>Fill out all required information to apply. You will recieve an email to confirm your provided email address is correct. You will also receive an email verify your account has been created.</li>
  </ul>
</section>

<section>
  <form name="register" action="/_register/new_customer" method="POST">
    <input type="hidden" name="method" value="register">
    <ul class="connectBorder infoText">
      <li><r7_page.message id=100></li>
    </ul>

    <?php	if ($page->errorCount()) { ?>
      <section id="form-message">
        <ul class="connectBorder errorText"><li><?=$page->errorString()?></li></ul>
      </section>
    <?php	} ?>

    <h3>Company/Organization Name:</h3>
    <ul id="registerCompanyName" class="form-grid connectBorder">
      <li>
        <input type="text" class="value registerValue long-field" name="organization_name" value="<?=!empty($_REQUEST['organization_name']) ? $_REQUEST['organization_name'] : "" ?>" placeholder="Company LLC" maxlength="50" required /></li>
      <li>
        <input id="is_reseller_checkbox" type="checkbox" name="reseller" value="yes" style="display: inline;" onChange="checkReseller();">Are you a reseller? (wish sell our products and services)</li>
    </ul>

    <h3>Register your Product</h3>
    <section>
      <ul id="serial_number_message" class="connectBorder errorText"><li>Serial number not found in our system</li></ul>
    </section>

    <section>
      <ul id="serial_number_message_ok" class="connectBorder progressText"><li>Serial number has been found</li></ul>
    </section>

    <ul class="form-grid four-col connectBorder">
      <li>
        <input id="has_item_checkbox" type="checkbox" name="reseller" value="yes" style="display: inline;" onChange="checkRegisterProduct();">Already have a device you would like to register?
      </li>
      <li id="product_type" style="display: none;"> 
        <label for="product">Product:</label>
        <select id="product_id" name="product_id" class="value input collectionField" style="display: block" onchange="document.getElementById('serial_number_message').style.display = 'none';">
          <option value="" <?php	if (isset($selectedProduct) && $product==$selectedProduct) print " selected"; ?>>---</option>
          <?php	foreach ($productsAvailable as $product) { ?>
            <option value="<?=$product->id?>" <?php	if (isset($selectedProduct) && $product->id == $selectedProduct) print " selected"; ?>>
            <?=$product->code?> -
            <?=$product->description?>
            </option>
          <?php	} ?>
        </select>
      </li>
      <li id="product_serial" style="display: none;">
        <label for="serialNum">Serial #</label>
        <input type="text" id="serial_number" class="long-field" name="serial_number" placeholder="Serial Number" onfocus="checkProduct();" onchange="checkSerial()" maxlength="50">
      </li>
    </ul>

    <h3>Business Address</h3>
    <ul class="form-grid four-col connectBorder">
      <li>
        <label for="country_id">Country</label>
        <select id="country_id" class="long-field" name="country_id" onChange="getProvinces()">
		<?php	foreach($countries as $country) { ?>
			<option value="<?=$country->id?>"<?php if ($country->id == $_REQUEST['country_id']) print " selected";?>><?= $country->name?></option>
		<?php	} ?>
		</select>
      </li>
      <li>
        <label for="state">State/Region</label>
        <select id="province_id" name="province_id">
		<?php	foreach($provinces as $province) { ?>
			<option value="<?=$province->id?>"<?php if ($province->id == $_REQUEST['province_id']) print " selected";?>><?= $province->name?></option>
		<?php	} ?>
		</select>
      </li>
      <li>
        <label for="address">Address</label>
        <input type="text" id="address" class="long-field" name="address" placeholder="" value="<?=!empty($_REQUEST['address']) ? $_REQUEST['address'] : "" ?>" maxlength="50" />
      </li>
      <li>
        <label for="city">City</label>
        <input type="text" id="city" name="city" placeholder="" value="<?=!empty($_REQUEST['city']) ? $_REQUEST['city'] : "" ?>" maxlength="50" />
      </li>
      <li>
        <label for="zip">Zip/Postal Code</label>
        <input type="text" id="zip" name="zip" placeholder="" value="<?=!empty($_REQUEST['zip']) ? $_REQUEST['zip'] : "" ?>" maxlength="20" />
      </li>
    </ul>

    <h3>Contact Info</h3>
    <ul class="form-grid four-col connectBorder">
      <li>
        <label for="firstname">First Name:</label>
        <input type="text" class="value registerValue registerFirstNameValue long-field" name="first_name" value="<?=!empty($_REQUEST['first_name']) ? $_REQUEST['first_name'] : "" ?>" placeholder="" maxlength="50" required/>
      </li>
      <li>
      <label for="lastname">Last Name:</label>
        <input type="text" class="value registerValue registerLastNameValue long-field" name="last_name" value="<?=!empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : "" ?>" placeholder="" maxlength="50" required/>
      </li>
      <li>
        <label for="state" style="clear: both">Phone Number</label>
        <select name="phone_type" class="input contactTypeInput">
          <option value="Business Phone">Business Phone</option>
          <option value="Home Phone">Home Phone</option>
          <option value="Mobile Phone">Mobile Phone</option>
        </select>
        <input type="text" id="phone" name="phone_number" placeholder="555-555-5555" value="<?=!empty($_REQUEST['phone_number']) ? $_REQUEST['phone_number'] : "" ?>" maxlength="50" />
      </li>
      <li>
        <label for="state" style="clear: both">Email Address:</label>
        <select name="email_type" class="input contactTypeInput">
          <option value="Work Email">Work Email</option>
          <option value="Home Email">Home Email</option>
        </select>
        <input type="email" id="email" name="email_address" value="<?=!empty($_REQUEST['email_address']) ? $_REQUEST['email_address'] : "" ?>" placeholder="me@business.com" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,63}$" maxlength="50" required/>
      </li>
      <li>
        <label for="username">Username:</span>
        <input type="text" id="login" style="<?=isset($page->loginTaken) ? 'border:solid red 2px;' : ''?> display:inline;" name="login" value="<?=!empty($_REQUEST['login']) ? $_REQUEST['login'] : "" ?>" onchange="checkUserName()" maxlength="50" required/>
      </li>
      <li>
        <label for="password">Create Password:</span>
        <input id="password" type="password" name="password" required/>
        <div id="password-message"></div>
      </li>
      <li>
        <label for="password2">Confirm Password:</label>
        <input type="password" name="password_2" required/>
      </li>
    </ul>

    <?php	if (isset($page->loginTaken)) { ?>
      <section>
        <ul id="login-message" class="connectBorder errorText">
          <li><?=$page->error;?></li>
        </ul>
      <section>
    <?php	} ?>

    <div id="registerSubmit" class="registerQuestion">
      <?php
      if (!$page->captchaPassed) {
      ?>
        <div style="color:red; font-size: 12px; padding-top:15px;">
          <?=$page->error;?>
        </div>
      <?php    
      }
      ?>
      <div class="g-recaptcha" data-sitekey="<?=$GLOBALS['_config']->captcha->public_key?>"></div>
      <input type="submit" class="button" onclick="return submitForm();" value="Apply" style="height: 35px; width: 90px;">
      <a class="button secondary" href="/_register/login">Cancel</a>
    </div>
  </form>
</section>
<?php
}
