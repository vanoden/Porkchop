<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
       $("#is_reseller_checkbox").change(function() {
      	    $("#assigned_reseller_id").toggle();
       });
       $("#has_item_checkbox").change(function() {
      	    $("#product_details").toggle();
       });
   });
</script>
<style>
   .long-field {
    min-width: 350px;
   }
   .small-text {
    font-size: 12px;
   }
</style>
<h1><i class="fa fa-users" aria-hidden="true"></i> NEW Customer Registration</h1>
<span class="form_instruction">Fill out all required information to apply. You will receive and email once your account has been created, thank you!</span><br/><br/>
<form name="register" action="/_register/new_customer" method="POST">
   <input type="hidden" name="method" value="register">
   <div class="instruction">
      <r7_page.message id=100>
   </div>
   <?php	if ($page->error) { ?>
    <div class="form_error"><?=$page->error?></div>
   <?php	} ?>
   <div id="registerFormSubmit">
      <div class="form-group">
         <div id="registerCompanyName">
            <span class="label registerLabel long-field"><strong>*Company/Organization Name:</strong></span>
            <input type="text" class="value registerValue long-field" name="organization_name" value="<?=!empty($_REQUEST['first_name']) ? $_REQUEST['first_name'] : "" ?>" placeholder="Company LLC"/>
            <div class="small-text">
               <input id="is_reseller_checkbox" type="checkbox" name="reseller" value="yes"> Are you a reseller? (wish sell our products and services)<br/>
            </div>
            <select id="assigned_reseller_id" name="assigned_reseller_id" class="wide_100per" style="display:none;">
               <?php	foreach ($resellers as $reseller) {
                  if ($organization->id == $reseller->id) continue;
                  ?>
               <option value="<?=$reseller->id?>"<?php if($organization->reseller->id == $reseller->id) print " selected";?>><?=$reseller->name?></option>
               <?php	} ?>
            </select>
         </div>
         <br/>
         <h3>Register your Product</h3>
         <div class="small-text">
            <input id="has_item_checkbox" type="checkbox" name="reseller" value="yes"> Already have a device you would like to register?<br/>
         </div>
         <div id="product_details" style="display:none;">
            <span class="label"><i class="fa fa-barcode" aria-hidden="true"></i> Serial #</span>
            <input type="text" id="serial_number" class="long-field" name="serial_number" placeholder="SF8TA1001">
            <span class="label"><i class="fa fa-cog" aria-hidden="true"></i> Product:</span>
            <select id="product_id" name="product_id" class="value input collectionField">
               <option value=""<? if ($product == $selectedProduct) print " selected"; ?>>---</option>
               <?php	foreach ($productsAvailable as $product) { ?>
                <option value="<?=$product[0]?>"<? if ($product[0] == $selectedProduct) print " selected"; ?>><?=$product[2]?> [Item: <?=$product[1]?>]</option>
               <?php	} ?>
            </select>
         </div>
         <br/>
         <h3>Business Address</h3>
         <label for="address"><i class="fa fa-address-card-o"></i> Address</label>
         <input type="text" id="address" class="long-field" name="address" placeholder="542 W. 15th Street">
         <label for="city"><i class="fa fa-institution"></i> City</label>
         <input type="text" id="city" name="city" placeholder="New York">
         <label for="state">State</label>
         <input type="text" id="state" name="state" placeholder="NY">
         <label for="zip">Zip</label>
         <input type="text" id="zip" name="zip" placeholder="10001">
         <label for="state">Buisness Phone</label>
         <input type="text" id="phone" name="phone" placeholder="555-555-5555">
         <label for="state">Cell</label>
         <input type="text" id="cell" name="cell" placeholder="555-555-5555"><br/>
         <h3>Contact Info</h3>
         <span class="label registerLabel registerFirstNameLabel">*First Name:</span>
         <input type="text" class="value registerValue registerFirstNameValue long-field" name="first_name" value="<?=!empty($_REQUEST['first_name']) ? $_REQUEST['first_name'] : "" ?>" placeholder="John">
         <span class="label registerLabel registerLastNameLabel">*Last Name:</span>
         <input type="text" class="value registerValue registerLastNameValue long-field" name="last_name" value="<?=!empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : "" ?>" placeholder="Doe">
         <span class="label registerLabel registerLoginLabel">*Login:</span>
         <input type="text" class="value registerValue registerLoginValue" name="login" value="<?=!empty($_REQUEST['login']) ? $_REQUEST['login'] : "" ?>" />
         <span class="label registerLabel registerPasswordLabel">*Password:</span>
         <input type="password" class="value registerValue registerPasswordValue" name="password" /><br/>
         <span class="label registerLabel registerPasswordLabel">*Confirm Password:</span>
         <input type="password" class="value registerValue registerPasswordValue" name="password_2" /><br/>
         <span class="label registerLabel registerLoginLabel">*Work Email:</span>
         <input type="text" class="value registerValue registerLoginValue" name="work_email" value="<?=!empty($_REQUEST['work_email']) ? $_REQUEST['work_email'] : "" ?>" placeholder="me@business.com">
         <span class="label registerLabel registerLoginLabel">*Home Email:</span>
         <input type="text" class="value registerValue registerLoginValue" name="home_email" value="<?=!empty($_REQUEST['home_email']) ? $_REQUEST['home_email'] : "" ?>" placeholder="me@email.com">
      </div>
      <div id="registerSubmit" class="registerQuestion">
         <div class="g-recaptcha" data-sitekey="6LfrepcUAAAAACr1RpIeYIUasYuF0vC13wkDQgrN"></div>
         <br/><input type="submit" class="button" onclick="return submitForm();" value="Apply" style="height: 35px; width: 90px;"><br/><br/>
         <a class="button secondary" href="/_register/login">Cancel</a>
      </div>
   </div>
   <!-- end registerFormSubmit -->
   </div>
</form>
