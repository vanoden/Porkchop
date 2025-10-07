<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="/js/monitor.js"></script>
<script src="/js/geography.js"></script>
<script src="/js/dom-utils.js"></script>
<script type="text/javascript">

// validate email from user
function validateEmail(email) {
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) return true;
    alert("Email: " + email + " is not valid")
    return false;
}

// validate password and submit if ok to go
function submitForm() {
  var emailField = document.getElementById("email");
  if (!validateEmail(emailField.value)) {
    console.log("email is not valid");
    return false;
  } else {
    console.log("email is good");
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
    var serialMessageOK = document.getElementById('serial_number_message_ok');
    
    if (productElem.selectedIndex <= 0) {
        serialMessage.innerHTML = 'Select a product first';
        serialMessage.style.display = 'block';
        serialMessageOK.style.display = 'none';
        return false;
    }
    
    var productID = productElem.options[productElem.selectedIndex].value;
    
    // Make API call to validate product using Product API directly
    var params = 'method=getItem&id=' + encodeURIComponent(productID);
    apiRequest('/_product/api', params, function(response, error) {
        if (error) {
            serialMessage.innerHTML = 'Error validating product. Please try again.';
            serialMessage.style.display = 'block';
            serialMessageOK.style.display = 'none';
            console.error('Product validation error:', error);
        } else if (response && response.item) {
            // Product is valid, hide any existing messages
            serialMessage.style.display = 'none';
            serialMessageOK.style.display = 'none';
            
            // If there's a serial number entered, validate it
            if (document.getElementById('serial_number').value.length > 0) {
                checkSerial();
            }
        } else {
            serialMessage.innerHTML = 'Product not found';
            serialMessage.style.display = 'block';
            serialMessageOK.style.display = 'none';
        }
    });
    
    return false; // Always return false since this is async
}

// make sure that the user name isn't taken
function checkUserName() {
    var loginField = document.getElementById("login");
    var loginMessage = document.getElementById("login-message");
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/_register/api?method=checkLoginNotTaken&login=' + encodeURIComponent(loginField.value), true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var data = xhr.responseText;
            if (data == 1) {
                loginField.style.border = '2px solid green';
                loginMessage.innerHTML = 'login is available';
                loginMessage.style.color = 'green';
            } else {
                loginField.style.border = '2px solid red';
                loginMessage.innerHTML = 'login is not available';
                loginMessage.style.color = 'red';
            }
        }
    };
    xhr.send();
}

// Check password strength
function checkPasswordStrength() {
  var customer = Object.create(Customer);
  var passwordField = document.getElementById('password');
  var passwordMessage = document.getElementById('password-message');
  if (customer.checkPasswordStrength(passwordField.value)) {
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
  var code = serialInput.value.trim();
  serialInput.value = code;

  // Check if Asset object is available
  if (typeof Asset === 'undefined') {
    console.log('Asset object not available, skipping serial number validation');
    return true;
  }

  try {
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
  } catch (error) {
    console.log('Error checking serial number:', error);
    // If there's an error, just return true to allow the form to continue
    return true;
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
    // Clear any existing serial number validation messages
    document.getElementById('serial_number_message').style.display = 'none';
    document.getElementById('serial_number_message_ok').style.display = 'none';
    // Clear the serial number field
    document.getElementById('serial_number').value = '';
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
  if (isset($isVerifedAccount)) {
    if ($isVerifedAccount) {
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
        <section id="form-message">
			<ul class="connectBorder errorText"><?=$page->errorString()?></ul>
		</section>
		<h2>Your account could not be verified</h2>
        <h5>Please check your <strong>spam / other</strong> mail folders in case you still need to find the correct verification link.</h5>
        <form name="register" action="/_register/new_customer" method="POST">
            <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
            <input type="hidden" name="method" value="resend">
            <input type="hidden" name="login" value="<?=$_REQUEST['login'];?>">
            <input type="submit" class="button register-new-customer-resend-button" value="Resend Email">
        </form>
      <?php
    }
  } else {
  ?>

<section id="form-message">
<h1 class="pageSect_full">New Customer Registration</h1>
  <ul class="connectBorder infoText">
    <li>Fill out all required information to apply. You will recieve an email to confirm your provided email address is correct. You will also receive an email verify your account has been created.</li>
  </ul>
</section>

<section>
  <form name="register" action="/_register/new_customer" method="POST">
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <input type="hidden" name="method" value="register">

    <?php	if ($page->errorCount()) { ?>
      <section id="form-message">
        <ul class="connectBorder errorText"><li><?=$page->errorString()?></li></ul>
      </section>
    <?php	} ?>

    <h2>Company/Organization Name:</h2>
    <ul id="registerCompanyName" class="form-grid connectBorder">
      <li>
        <input type="text" class="value registerValue long-field" name="organization_name" value="<?=!empty($_REQUEST['organization_name']) ? $_REQUEST['organization_name'] : "" ?>" placeholder="Company LLC" maxlength="50" required /></li>
      <li>
        <input id="is_reseller_checkbox" type="checkbox" name="reseller" value="yes" class="register-new-customer-reseller-checkbox" onChange="checkReseller();">Are you a reseller? (wish sell our products and services)</li>
    </ul>

    <h2>Register your Product</h2>
    <section>
      <ul id="serial_number_message" class="connectBorder errorText register-new-customer-serial-message" style="display: none;">
        <li>Serial number not found in our system</li>
      </ul>
    </section>

    <section>
      <ul id="serial_number_message_ok" class="connectBorder progressText register-new-customer-serial-message" style="display: none;">
        <li>Serial number has been found</li>
      </ul>
    </section>

    <ul class="form-grid four-col connectBorder">
      <li>
        <input id="has_item_checkbox" type="checkbox" name="reseller" value="yes" class="register-new-customer-reseller-checkbox" onChange="checkRegisterProduct();">Already have a device you would like to register?
      </li>
      <li id="product_type" class="register-new-customer-product-type" style="display: none;"> 
        <label for="product">Product:</label>
        <select id="product_id" name="product_id" class="value input collectionField register-new-customer-product-select" onchange="document.getElementById('serial_number_message').style.display = 'none'; document.getElementById('serial_number_message_ok').style.display = 'none';">
          <option value="" <?php	if (isset($selectedProduct) && $product==$selectedProduct) print " selected"; ?>>---</option>
          <?php	foreach ($productsAvailable as $product) { ?>
            <option value="<?=$product->id?>"<?php	if (isset($selectedProduct) && $product->id == $selectedProduct) print " selected"; ?>><?=$product->code?> - <?=strip_tags($product->description)?></option>
          <?php	} ?>
        </select>
      </li>
      <li id="product_serial" class="register-new-customer-product-serial" style="display: none;">
        <label for="serialNum">Serial #</label>
        <input type="text" id="serial_number" class="long-field" name="serial_number" placeholder="Serial Number" onmouseout="checkProduct();" onchange="checkSerial()" maxlength="50">
      </li>
    </ul>

    <h2>Business Address</h2>
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

    <h2>Contact Info</h2>
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
        <label for="state" class="register-new-customer-label-clear">Phone Number</label>
        <select name="phone_type" class="input contactTypeInput">
          <option value="Business Phone">Business Phone</option>
          <option value="Home Phone">Home Phone</option>
          <option value="Mobile Phone">Mobile Phone</option>
        </select>
        <input type="text" id="phone" name="phone_number" placeholder="555-555-5555" value="<?=!empty($_REQUEST['phone_number']) ? $_REQUEST['phone_number'] : "" ?>" maxlength="50" />
      </li>
      <li>
        <label for="state" class="register-new-customer-label-clear">Email Address:</label>
        <select name="email_type" class="input contactTypeInput">
          <option value="Work Email">Work Email</option>
          <option value="Home Email">Home Email</option>
        </select>
        <input type="email" id="email" name="email_address" value="<?=!empty($_REQUEST['email_address']) ? $_REQUEST['email_address'] : "" ?>" placeholder="me@business.com" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,63}$" maxlength="50" required/>
      </li>
      <li>
        <label for="username">Username:</span>
        <input type="text" id="login" class="<?=isset($loginTaken) ? 'register-new-customer-login-error' : 'register-new-customer-login-normal'?>" name="login" value="<?=!empty($_REQUEST['login']) ? $_REQUEST['login'] : "" ?>" onmouseout="checkUserName()" maxlength="50" required/>
        <div id="login-message"></div>
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

    <div id="registerSubmit" class="registerQuestion">
      <?php
      if (!$captcha_ok) {
      ?>
        <div class="register-new-customer-error-message">
          <?=$page->errorString()?>
        </div>
      <?php    
      }
      ?>
      <div class="g-recaptcha" data-sitekey="<?=$GLOBALS['_config']->captcha->public_key?>"></div>
      <input type="submit" class="button" onclick="return submitForm();" value="Apply">
      <a class="button btn-secondary" href="/_register/login">Cancel</a>
    </div>
  </form>
</section>
<?php
}
