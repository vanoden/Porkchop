<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="/js/monitor.js"></script>
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
            emailField.css('border', '2px solid red');
            emailField.css('color', 'red');
            return false;
        } else {
            emailField.css('border', '2px solid green');
            emailField.css('color', 'green');     
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

    // check reseller toggle
    $(document).ready(function() {
        $("#has_item_checkbox").change(function() {
            $("#product_details").toggle();
        });
    });

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
            serialNumberMessage.innerHTML = 'Serial number not found in our system';
            serialNumberMessage.style.display = 'block';
            serialNumberMessageOK.style.display = 'none';
            return false;
        }
    }
</script>
<style>
    .long-field {
        min-width: 350px;
    }
    
    .small-text {
        font-size: 12px;
    }
    
    div.contactContainer {
        padding: 1px;
        margin-left: 0px;
    }
    
    select.contactTypeInput {
        display: inline;
    }
    
    input#phone,
    input#email {
        display: inline;
    }
    
    span.registerPasswordLabel {
        display: block;
        clear: both;
    }
</style>
<?php
    if (isset($page->isVerifedAccount)) {
        if ($page->isVerifedAccount) {
?>
    <h3>Your account has been verified, thank you!</h3>
    <br>
    <h5>You may login to your account <a href="/_register/login">here</a>.</h5>
    <br/>
    <h6><i><strong>Note:</strong> Our account administrators will soon fully approve your account to use our platform.</i></h6>
    <?php        
        } else {
?>
        <h3>Account could not be verified</h3>
        <h5>Please check your <strong>spam / other</strong> mail folders in case you still need to find the correct verification link.</h5>
        <?php
        }
    } else {
?>
            <h1><i class="fa fa-users" aria-hidden="true"></i> NEW Customer Registration</h1>
            <span class="form_instruction">Fill out all required information to apply. You will receive an email once your account has been verified, thank you!</span>
            <br/>
            <br/>
            <form name="register" action="/_register/new_customer" method="POST">
                <input type="hidden" name="method" value="register">
                <div class="instruction">
                    <r7_page.message id=100>
                </div>
                <?php	if ($page->errorCount()) { ?>
                    <div class="form_error">
                        <?=$page->errorString()?>
                    </div>
                    <?php	} ?>
                        <div id="registerFormSubmit">
                            <div class="form-group">
                                <div id="registerCompanyName">
                                    <h3>*Company/Organization Name:</h3>
                                    <input type="text" class="value registerValue long-field" name="organization_name" value="<?=!empty($_REQUEST['organization_name']) ? $_REQUEST['organization_name'] : " " ?>" placeholder="Company LLC" maxlength="50" />
                                    <div class="small-text">
                                        <input id="is_reseller_checkbox" type="checkbox" name="reseller" value="yes"> Are you a reseller? (wish sell our products and services)
                                        <br/>
                                    </div>
                                </div>
                                <br/>
                                <h3>Register your Product</h3>
                                <div class="small-text">
                                    <input id="has_item_checkbox" type="checkbox" name="reseller" value="yes"> Already have a device you would like to register?
                                    <br/>
                                </div>
                                <div id="product_details" style="display:none;">
                                    <span class="label" style="display: block"><i class="fa fa-cog" aria-hidden="true"></i> Product:</span>
                                    <select id="product_id" name="product_id" class="value input collectionField" style="display: block" onchange="document.getElementById('serial_number_message').style.display = 'none';">
                                        <option value="" <? if (isset($selectedProduct) && $product==$selectedProduct) print " selected"; ?>>---</option>
                                        <?php	foreach ($productsAvailable as $product) { ?>
                                            <option value="<?=$product->id?>" <? if (isset($selectedProduct) && $product->id == $selectedProduct) print " selected"; ?>>
                                                <?=$product->code?> -
                                                    <?=$product->description?>
                                            </option>
                                            <?php	} ?>
                                    </select>
                                    <span class="label"><i class="fa fa-barcode" aria-hidden="true"></i> Serial #</span>
                                    <input type="text" id="serial_number" class="long-field" name="serial_number" placeholder="Serial Number" onfocus="checkProduct();" onchange="checkSerial()" maxlength="50">
                                    <div id="serial_number_message" style="color:red; display:none;">Serial number not found in our system
                                        <br/>
                                        <br/>
                                    </div>
                                    <div id="serial_number_message_ok" style="color:green; display:none;">Serial number has been found, thank you for providing!
                                        <br/>
                                        <br/>
                                    </div>
                                </div>
                                <br/>

                                <h3>Business Address</h3>
                                <label for="address"><i class="fa fa-address-card-o"></i> Address</label>
                                <input type="text" id="address" class="long-field" name="address" placeholder="542 W. 15th Street" value="<?=!empty($_REQUEST['address']) ? $_REQUEST['address'] : " " ?>" maxlength="50" />
                                <label for="city"><i class="fa fa-institution"></i> City</label>
                                <input type="text" id="city" name="city" placeholder="New York" value="<?=!empty($_REQUEST['city']) ? $_REQUEST['city'] : " " ?>" maxlength="50" />
                                <label for="state">State/Region</label>
                                <input type="text" id="state" name="state" placeholder="NY" value="<?=!empty($_REQUEST['state']) ? $_REQUEST['state'] : " " ?>" maxlength="10" />
                                <label for="zip">Zip/Postal Code</label>
                                <input type="text" id="zip" name="zip" placeholder="10001" value="<?=!empty($_REQUEST['zip']) ? $_REQUEST['zip'] : " " ?>" maxlength="20" />
                                <br>
                                <h3>Contact Info</h3>
                                <span class="label registerLabel registerFirstNameLabel">*First Name:</span>
                                <input type="text" class="value registerValue registerFirstNameValue long-field" name="first_name" value="<?=!empty($_REQUEST['first_name']) ? $_REQUEST['first_name'] : " " ?>" placeholder="John" maxlength="50" />
                                <span class="label registerLabel registerLastNameLabel">*Last Name:</span>
                                <input type="text" class="value registerValue registerLastNameValue long-field" name="last_name" value="<?=!empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : " " ?>" placeholder="Doe" maxlength="50" />
                                <label for="state" style="clear: both">Phone Number</label>
                                <div class="container contactContainer">
                                    <select name="phone_type" class="input contactTypeInput">
                                        <option value="Business Phone">Business Phone</option>
                                        <option value="Home Phone">Home Phone</option>
                                        <option value="Mobile Phone">Mobile Phone</option>
                                    </select>
                                    <input type="text" id="phone" name="phone_number" placeholder="555-555-5555" value="<?=!empty($_REQUEST['phone_number']) ? $_REQUEST['phone_number'] : " " ?>" maxlength="50" />
                                </div>
                                <span class="label registerLabel registerLoginLabel">*Email Address:</span>
                                <div class="container contactContainer">
                                    <select name="email_type" class="input contactTypeInput">
                                        <option value="Work Email">Work Email</option>
                                        <option value="Home Email">Home Email</option>
                                    </select>
                                    <input type="text" id="email" class="value registerValue registerLoginValue" name="email_address" value="<?=!empty($_REQUEST['email_address']) ? $_REQUEST['email_address'] : " " ?>" placeholder="me@business.com" maxlength="50" />
                                </div>
                                <span class="label registerLabel registerLoginLabel">*Login:</span>
                                <input type="text" id="login" class="value registerValue registerLoginValue" style="<?=($page->loginTaken) ? 'border:solid red 2px;' : ''?> display:inline;" name="login" value="<?=!empty($_REQUEST['login']) ? $_REQUEST['login'] : " " ?>" onchange="checkUserName()" maxlength="50" />
                                <div id="login-message" style="display:inline; font-size: 10px;"></div>
                                <?php
                                    if ($page->loginTaken) {
                                 ?>
                                    <div style="color:red; font-size: 12px;">
                                        <?=$page->error;?>
                                    </div>
                                    <br/>
                                    <?php
                                         }
                                     ?>
                                        <span class="label registerLabel registerPasswordLabel">*Password:</span>
                                        <input type="password" class="value registerValue registerPasswordValue" name="password" />
                                        <br/>
                                        <span class="label registerLabel registerPasswordLabel">*Confirm Password:</span>
                                        <input type="password" class="value registerValue registerPasswordValue" name="password_2" />
                                        <br/>
                            </div>
                            <div id="registerSubmit" class="registerQuestion">
                                <?php
                                    if (!$page->captchaPassed) {
                                 ?>
                                    <div style="color:red; font-size: 12px; padding-top:15px;">
                                        <?=$page->error;?>
                                    </div>
                                    <br/>
                                    <?php    
                                         }
                                     ?>
                                        <div class="g-recaptcha" data-sitekey="<?=$GLOBALS['_config']->captcha->public_key?>"></div>
                                        <br/>
                                        <input type="submit" class="button" onclick="return submitForm();" value="Apply" style="height: 35px; width: 90px;">
                                        <br/>
                                        <br/>
                                        <a class="button secondary" href="/_register/login">Cancel</a>
                            </div>
                        </div>
                        <!-- end registerFormSubmit -->
                        </div>
            </form>
            <?php
    }
?>
