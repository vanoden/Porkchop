<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="/js/geography.js" type="text/javascript"></script>
<style>
    * {
     box-sizing: border-box;
    }
    
    .row {
         display: -ms-flexbox;
         display: flex;
         -ms-flex-wrap: wrap;
         flex-wrap: wrap;
         margin: 0 -16px;
    }
    
    .col-25 {
         -ms-flex: 25%;
         flex: 25%;
    }
    
    .col-50 {
         -ms-flex: 50%;
         flex: 50%;
    }
    
    .col-75 {
         -ms-flex: 75%;
         flex: 75%;
    }
    
    .col-25, .col-50, .col-75 {
         padding: 0 16px;
    }
    
    .label {
         font-weight: bold;
    }
    
    .container {
         padding: 5px 20px 15px 20px;
         border-radius: 3px;
    }
    
    input[type=text] {
         width: 100%;
         margin-bottom: 20px;
         padding: 12px;
         border: 1px solid #ccc;
         border-radius: 3px;
    }
    
    .small-input {
         width: 50%;
    }
    
    .enter-shipping-form {
         border: dashed 1px #000;
         padding: 10px;
         max-width: 700px;
    }
    label {
         margin-bottom: 10px;
         display: block;
    }
    
    .icon-container {
         margin-bottom: 20px;
         padding: 7px 0;
         font-size: 24px;
    }
    
    #submit-form-button {
         background-color: #4CAF50;
    }
    
    #submit-form-button:hover {
         background-color: #45a049;
    }
    
    .btn {
         background-color: #4CAF50;
         color: white;
         padding: 20px;
         margin: 10px 0;
         border: none;
         width: 100%;
         border-radius: 3px;
         cursor: pointer;
         font-size: 17px;
    }
    
    .btn:hover {
         background-color: #45a049;
    }
    
    a {
         color: #2196F3;
    }
    
    hr {
         border: 1px solid lightgrey;
    }
    
    span.price {
         float: right;
         color: grey;
    }
    
    @media ( max-width : 800px) {
         .row {
             flex-direction: column-reverse;
        }
         .col-25 {
             margin-bottom: 20px;
        }
    }
    
    .tableBody, .tableTitle {
         display: table;
         width: 100%;
         max-width: 800px;
         border-color: #dedede;
         border-style: solid;
    }
    
    .tableBody.half, .tableTitle.half {
         max-width: 400px;
    }
    
    .tableBody {
         border-width: 1px;
    }
    
    .tableBody.clean {
         border: none;
    }
    
    .tableBody.clean>.tableRowHeader {
         background: none;
    }
    
    .tableTitle {
         margin-top: 20px;
         background: #f0f3f5;
         border-width: 1px 1px 0 1px;
    }
    
    .tableCell {
         display: table-cell;
         text-align: left;
         vertical-align: middle;
         padding: 3px 7px 2px;
    }
    
    .tableTitleLeft {
         float: left;
    }
    
    .tableTitleRight {
         text-align: right;
    }
    
    .tableRowHeader {
         display: table-row;
    }
    
    .tableRowFooter {
         display: table-row;
         width: 800px;
         text-align: center;
    }
    
    .tableBodyWrapper {
         width: 100%;
         max-width: 800px;
         height: 150px;
         overflow-y: auto;
    }
    
    .tableBodyScrolled {
         display: table;
         max-width: 800px;
    }
    
    .tableRow {
         display: table-row;
    }
    
    .tableRow:nth-child(odd) {
         background-color: #eeeff7;
    }
    
    .min-tablet {
         min-width: 600px;
         max-width: 1000px;
    }
    
    .tableCell textarea {
         width: 100%;
         padding: 2px 4px;
    }
    
    .value {
         color: #6495ed;
    }
    
    #page-mgmt #submit-form-button, #page-mgmt #show-billing-button, #page-mgmt #show-checklist-button {
         padding: 10px;
         max-width: 50%;
         margin: unset;
         margin: auto;
    }
    
    #support_rma .stepwizard-step a {
         color: white;
    }
    
    .btn-circle {
         width: 30px;
         height: 30px;
         text-align: center;
         padding: 6px 0;
         font-size: 12px;
         line-height: 1.428571429;
         border-radius: 15px;
    }
    
    .green {
         color: green;
    }
    
    .red {
         color: red;
    }

	#rma_date_received {
		width: 200px;
	}
	#rma_condition_received {
		width: 200px;
	}
</style>
<script type="text/javascript">
	// show the billing address form, step 2
	function showBilling() {
		document.getElementById('billing_contact_form').style.display = 'block';
		document.getElementById('show-billing-button').style.display = 'none';
		document.getElementById('show-checklist-button').style.display = 'block';
	}
   
	// show step 3 terms of conditions for the return
	function showTerms() {
		document.getElementById('checklist_form').style.display = 'block';
		document.getElementById('show-checklist-button').style.display = 'none';
		document.getElementById('submit-form-button').style.display = 'block';
	}
   
	// check if a shipping field is populated by id
	function checkFieldsArray(elementArray) {
		for (var i = 0; i < elementArray.length; i ++) {
			console.log(elementArray[i]);
			if (!document.getElementById(elementArray[i]).value) {
				console.log(elementArray[i].id + " not filled in");
				return false;
			}
			else {
				console.log(elementArray[i].id + " completed");
			}
		}
		return true;
	}
   
	// validate and submit form
	var shippingFieldsPopulated = true;
	var billingFieldsPopulated = true;
	function submitForm() {
		var shippingFields = ['shipping_address', 'shipping_city', 'shipping_zip'];
		var billingFields = ['billing_firstname', 'billing_email', 'billing_phone'];
   
		// show shipping fields are required, unless they pick an existing address
		var shippingFieldItems = Array.prototype.slice.call(document.getElementsByClassName("shipping_fields"));
		if (checkFieldsArray(shippingFields) || document.getElementById('shipping_address_picker').value > 0) {
			shippingFieldItems.forEach(function (element){updateFieldBackground(element, '#f0f8ff')});
			document.getElementById("shipping_fields_required").style.display="none";
		}
		else {
			shippingFieldItems.forEach(function (element){updateFieldBackground(element, 'rgba(240, 173, 140, 0.60)')});
			document.getElementById("shipping_fields_required").style.display="block";
			return false;
		}
       
		// show billing fields are required, if the they didn't check same as shipping
		var billingFieldItems = Array.prototype.slice.call(document.getElementsByClassName("billing_fields"));
		if (checkFieldsArray(billingFields) || document.getElementById('billing_contact_picker').value > 0) {
			billingFieldItems.forEach(function (element){ updateFieldBackground(element, '#f0f8ff'); });
			document.getElementById("billing_fields_required").style.display="none";
		}
		else {
			billingFieldItems.forEach(function (element){ updateFieldBackground(element, 'rgba(240, 173, 140, 0.60)'); });
			document.getElementById("billing_fields_required").style.display="block";
			return false;
		}

		// confirm terms of RMA are required
		var confirmTermsItems = Array.prototype.slice.call(document.getElementsByClassName("confirm_terms"));
		if (!document.getElementById("agree_package_properly").checked || !document.getElementById("agree_payment_received").checked) {
			document.getElementById("agree_terms_message").style.display="block";
			confirmTermsItems.forEach(function (element){updateMessageColors(element, 'red')});
			return false;
		}
		else {
			document.getElementById("agree_terms_message").style.display="none";
			confirmTermsItems.forEach(function (element){updateMessageColors(element, 'black')});
		}
		document.getElementById("submit_rma_form").submit();
	}
   
	// update the field background colors
	function updateFieldBackground(element, color) {
		element.style.background = color;
	}
   
	// update the message colors
	function updateMessageColors(element, color) {
		element.style.color = color;
	}
   
	// get the dropdown name of the selected option
	function getDropdownSelectedText(dropdownId) {
		var domNode = document.getElementById(dropdownId);
		var value = domNode.selectedIndex;
		return domNode.options[value].text;
	}

	// country has been selected
	function changeCountry(countryDropdownId, addressContainer, provinceDropdownId, provinceAddressContainer) {
		var countryDropdown = document.getElementById(countryDropdownId);
		var provinceDropdown = document.getElementById(provinceDropdownId);
		document.getElementById(addressContainer).style.display = "none";
		var provinceList = Object.create(ProvinceList);
		var countryId = countryDropdown.value;
		var provinces = provinceList.find({country_id:countryId});
		console.log(provinces);
		document.getElementById(provinceAddressContainer).style.display = "block";
		provinceDropdown.innerHTML = '';
		var defOpt = document.createElement('option');
		defOpt.value = 0;
		defOpt.name = "Select";
		provinceDropdown.appendChild(defOpt);
		if (provinces.length > 0) {
			for (var i = 0; i < provinces.length; i++) {
				var opt = document.createElement('option');
				opt.value = provinces[i].id;
				opt.innerHTML = provinces[i].name;
				provinceDropdown.appendChild(opt);
			}
			//else {
			//	document.getElementById('provinceDropdownId').append('<option value="0">' + getDropdownSelectedText(countryDropdownId) + '</option>');
			//}
		};
	}
   
	// a province has been selected
	function changeProvince(addressContainer) {
		document.getElementById(addressContainer).style.display = "block";
	}
   
	// choose a shipping address, hide / show form fields
	function selectShippingAddress() {
		if (document.getElementById('shipping_address_picker').selectedIndex > 1) {
			console.log('Existing Shipping address selected');
			document.getElementById('add_new_shipping_address').style.display = "none";
			document.getElementById('billing_contact_form').style.display = "none";
			document.getElementById('show-checklist-button').style.display = "none";
			document.getElementById('billing_contact_form').style.display = "none";
			document.getElementById('checklist_form').style.display = "none";
			document.getElementById('submit-form-button').style.display = "none";
		}
		else {
			console.log('New Shipping address Selected');
			document.getElementById('add_new_shipping_address').style.display = "block";
		}

		if (document.getElementById('shipping_address_picker').value == '') {
			document.getElementById('add_new_shipping_address').style.display = "none";
			document.getElementById('billing_contact_form').style.display = "none";
			document.getElementById('show-checklist-button').style.display = "none";
			document.getElementById('add_new_billing_contact').style.display = "none";
			document.getElementById('show-billing-button').style.display = "none";
			document.getElementById('checklist_form').style.display = "none";
			document.getElementById('submit-form-button').style.display = "none";
		}
		else {
			document.getElementById('show-billing-button').style.display = "block";
		}
	}
   
   // choose a billing address, hide / show form fields
   function selectBillingContact() {
		if (document.getElementById('billing_contact_picker').value > 0) {
			document.getElementById('dd_new_billing_contact').style.display = "none";
			document.getElementById('checklist_form').style.display = "none";
			document.getElementById('submit-form-button').style.display = "none";
		}
		else {
			document.getElementById('add_new_billing_contact').style.display = "block";
		}
		if (document.getElementById('billing_contact_picker').value == '') {
			document.getElementById('show-checklist-button').style.display = "none";
			document.getElementById('add_new_billing_contact').style.display = "none";
			document.getElementById('show-billing-button').style.display = "none";
			document.getElementById('checklist_form').style.display = "none";
			document.getElementById('submit-form-button').style.display = "none";
		}
		else {
			document.getElementById('show-checklist-button').style.display = "block";
		}
	}
    
    // radio button checks to hide location name for 'personal address'
	document.addEventListener("DOMContentLoaded", function (event) {
		if (document.querySelector('input[type=radio][name=shipping_address_type]')) {
			document.querySelector('input[type=radio][name=shipping_address_type]').addEventListener("change",function() {
				if (this.value == 'personal') {
					document.getElementById('shipping-radio-container').style.display = "none";
				} else {
					document.getElementById('shipping-radio-container').style.display = "block";
				}
			});
		}
    });
</script>
<?php	if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div><br/><br/>
<?php	} else if (!empty($page->success)) {?>
<div class="form_success"><?=$page->success?></div><br/><br/>
<?php	} ?>
<h1>Return Merchandise Authorization</h1>

<!-- Form Messaging -->
<div id="support_rma">
	<h2 class="green"><?=$rmaMessage?></h2>
<?php	if ($rmaReceived) { ?>
	<span class="value">Received <?=$shippingPackage->date_received?> by <?=$shippingPackage->user_received()->full_name()?></span>
<?php	} ?>
</div>
<?php	if ($rmaSubmitted && ! $rmaReceived) { ?>
<div class="container">
	<span class="container"> <span class="label"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> 
	Please include the following form with your return: </span> <span class="value">&nbsp;<a href="/_support/rma_pdf/<?=$rmaCode?>" target="_blank"> <i class="fa fa-file"></i> Download </a></span>
	</span>
</div>
<?php	} else if ($rmaSubmitted) { ?>
<div>
    <span class="container" style="float: right;"> <span class="label"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> 
    Reprint RMA Document: </span> <span class="value">&nbsp;<a href="/_support/rma_pdf/<?=$rmaCode?>" target="_blank"> <i class="fa fa-file"></i> Download </a></span>
    </span>
</div>
<?php	} ?>

<!-- Addresses -->
<?php	if ($rmaSubmitted) { ?>
<div>
	<span class="label"><i class="fa fa-address-card" aria-hidden="true"></i> Sending From: <br /></span> <span class="value">
	<?=$sentFromLocation->address_1?> <?=$sentFromLocation->address_2?><br />
	<?=$sentFromLocation->city?>, <?=$sentFromLocation->zip_code?><br /> <i><?=$sentFromLocation->notes?></i>
	</span>
</div>
<div>
	<span class="label"><i class="fa fa-address-card-o" aria-hidden="true"></i> Shipping To: <br /></span> <span class="value">
       <?=$sentToLocation->address_1?> <?=$sentToLocation->address_2?><br />
       <?=$sentToLocation->city?>, <?=$sentToLocation->zip_code?><br /> <i><?=$sentToLocation->notes?></i>
	</span>
</div>
<?php	} ?>

<?php	if (!empty($shippingShipment->id)) { ?>
<!-- Package Received -->
        <div class="container">
           <span class="label"><i class="fa fa-envelope" aria-hidden="true"></i> Current Package Info: <br /></span> 
           <span class="value">
		   Vendor: <?=$shippingShipment->vendor()->name?><br/>
           Tracking #: <?=$shippingPackage->tracking_code;?><br/>
           </span>
        </div>
<?php	} ?>

<!-- Receipt Info -->
<?php	if ($GLOBALS['_SESSION_']->customer->can('receive shipments') && $rmaSubmitted && !$rmaReceived) { ?>
	<div class="container">
        <form method="post" id="submit_package_details">
		<input type="hidden" name="id" value="<?=$rma->id?>" />
		<span class="label">Date Received</span>
		<input id="rma_date_received" type="text" name="date_received" class="value input" value="<?=date('Y-m-d H:i:s')?>"/>
		<span class="label">Condition of Package</span>
		<select id="rma_condition_received" class="value input" style="display: block" name="condition">
			<option value="OK">OK</option>
			<option value="DAMAGED">Damaged</option>
		</select>
	<input type="submit" name="form_submitted" class="button" value="Receive Package" />
	</form>
</div>
<?php	} ?>

<!-- Ticket Info -->
<div class="container">
<span class="label"><i class="fa fa-ticket" aria-hidden="true"></i> Ticket</span> <a href="<?=$ticketLink?>" class="value"><?=$rmaTicketNumber?></a> / <?=$rmaNumber?></span>
</div>
<div class="container">
<span class="label"><i class="fa fa-user" aria-hidden="true"></i> Contact</span> <span class="value"><?=$rmaCustomerFullName?> - <?=$rmaCustomerOrganizationName?></span>
</div>
<div class="container">
<span class="label"><i class="fa fa-wrench" aria-hidden="true"></i> Approved By</span> <span class="value"><?=$rmaApprovedByName?> - <?=$rmaDateApproved?> - Status: <?=$rmaStatus?></span>
</div>
<div class="container">
<span class="label"><i class="fa fa-barcode" aria-hidden="true"></i> Product</span> <span class="value"><?=$rmaProduct->code?> - <a href="<?=$productLink?>"><?=$rmaSerialNumber?></a></span>
</div>

<?php	if ($showShippingForm) { ?>
<!-- Package Form -->
<div class="enter-shipping-form">
	<u><b><?=empty($shippingPackage->id) ? "Add" : "Update"?> your shipment info:</b></u>
	<form method="post" id="submit_package_details">
	<div class="small-input">
		<label for="tracking_code">Tracking Number</label> 
		<input type="text" id="tracking_code" name="tracking_code" class="tracking_code" placeholder="1Z9999999999999999" value="<?=$shippingPackage->tracking_code;?>">
		<label for="vendor_id">Shipping Vendor</label> 
		<select id="vendor_id" name="vendor_id" class="tracking_code" placeholder="10">
			<option value="">Select</option>
<?php		foreach ($shippingVendors as $shippingVendor) { ?>
			<option value="<?=$shippingVendor->id?>"<?php if ($shipment->vendor_id == $shippingVendor->id) print " selected";?>><?=$shippingVendor->name?></option>
<?php		} ?>
		<input type="hidden" name="form_submitted" value="package_details_submitted" />
		<input id="add-package-details" type="submit" value="<?=empty($shippingPackage->id) ? "Add" : "Update"?> Package Details" class="btn" style="height: 35px;">
	</div>
	</form>
</div>
<?php	} ?>

<?php	if (! $rmaSubmitted) { ?>
<div class="row">
<div class="col-75">
<div class="container">
 <form method="post" id="submit_rma_form">
    <div class="row">
       <div id="shipping_address_form" class="col-50">
	  Select your Location:
	  <select id="shipping_address_picker" name="shipping_address_picker" onchange="selectShippingAddress()">
	     <option value="">--</option>
	     <option value="0">[add new]</option>
	     <?php foreach ($customerLocations as $customerLocation) { ?>
	     <option value="<?=$customerLocation->id?>"><?=$customerLocation->name?> <?=$customerLocation->address_1?> <?=$customerLocation->address_2?> <?=$customerLocation->city?> <?=$customerLocation->zip_code?></option>
	     <?php } ?>
	  </select>
	  <div id="add_new_shipping_address" style="display:none;">
	     <input type="radio" name="shipping_address_type" value="business" checked="checked"> Business <input type="radio" name="shipping_address_type" value="personal"> Personal
	     <hr/>                 
	     <span id="shipping-radio-container">
		 <label for="fname"><i class="fa fa-building"></i> Location Name</label>
		 <input type="text" id="shipping_location_name" name="shipping_location_name" placeholder="Main Office / Warehouse"> 
	     </span>
	     <label for="adr"><i class="fa fa-address-card-o"></i> Address</label> 
	     <input type="text" id="shipping_address" name="shipping_address" class="shipping_fields" placeholder="542 W. 15th Street"> 
	     <input type="text" id="shipping_address2" name="shipping_address2" placeholder="Suite 1">
	     Select your Country:
	     <select id="shipping_country" name="shipping_country" onchange="changeCountry('shipping_country', 'shipping_address_container', 'shipping_province', 'shipping_province_container')">
		<option value="0">-</option>
		<?php 
		   foreach ($allCountriesList as $country) {
		   ?>
		<option value="<?=$country->id?>"><?=$country->name?></option>
		<?php
		   }
		   ?>
	     </select>
	     <div id="shipping_province_container" style="display: none;">
		<select id="shipping_province" name="shipping_province" onchange="changeProvince('shipping_address_container')">
		   <option value="0">-</option>
		</select>
	     </div>
	     <div id="shipping_address_container" style="display: none;">
		<label for="city"><i class="fa fa-institution"></i> City</label> 
		<input type="text" id="shipping_city" name="shipping_city" class="shipping_fields" placeholder="New York">
		<div class="row">
		   <div class="col-50">
		      <label for="zip">Zip</label> <input type="text" id="shipping_zip" name="shipping_zip" class="shipping_fields" placeholder="10001">
		   </div>
		</div>
	     </div>
	  </div>
       </div>
    </div>
    <input id="show-billing-button" onclick="showBilling()" type="button" value="Next" class="btn" style="display: none;"><br />
    <div id="billing_contact_form" class="row" style="display: none;">
       <div class="col-50">
	  <a name="billing">
	     <h2>Billing Contact</h2>
	  </a>
	  Select User:
	  <select id="billing_contact_picker" name="billing_contact_picker" onchange="selectBillingContact()">
	     <option value="">--</option>
	     <option value="0">[add new]</option>
	     <?php foreach ($organizationUsers as $organizationUser) { ?>
		<option value="<?=$organizationUser->id?>"<?php if ($organizationUser->id == $GLOBALS['_SESSION_']->customer->id) print " selected"; ?>><?=$organizationUser->first_name?> <?=$organizationUser->last_name?> </option>
	     <?php } ?>
	  </select>
	  <div id="add_new_billing_contact" style="display: none;">
	     <label for="fname"><i class="fa fa-user"></i> Full Name</label>
	     <input type="text" id="billing_firstname" name="billing_firstname" class="billing_fields" placeholder="John"> 
	     
	     <label for="fname"><i class="fa fa-user"></i> Last Name</label>
	     <input type="text" id="billing_lastname" name="billing_lastname" class="billing_fields" placeholder="Doe"> 
	     
	     <label for="fname"><i class="fa fa-envelope-o"></i> Email</label>
	     <input type="text" id="billing_email" name="billing_email" class="billing_fields" placeholder="user@email.com"> 
	     
	     <label for="fname"><i class="fa fa-phone"></i> Phone</label>
	     <input type="text" id="billing_phone" name="billing_phone" class="billing_fields" placeholder="123-555-5555"> 
	  </div>
       </div>
       <input id="show-checklist-button" onclick="showTerms()" type="button" value="Next" class="btn" style="display: none;"><br />
       <div id="checklist_form" class="row" style="display: none;">
	  <div class="col-50">
	     <a name="terms">
		<h3><u>Items Checklist</u></h3>
	     </a>
	     * Only the specified item may be returned. Other contents may be discarded<br /> <br /> <input type="checkbox" name="power_cord" value="power_cord"> Power Cord<br /> <input type="checkbox" name="filters" value="filters"> Filters<br /> <input type="checkbox" name="battery" value="battery"> Battery<br /> <input type="checkbox" name="carry_bag" value="carry_bag"> Carry Bag<br /> <input type="checkbox" name="usb_comm_cable" value="usb_comm_cable"> USB Comm Cable<br /> <input type="checkbox" name="cellular_access_point" value="cellular_access_point"> Cellular Access Point (MiFi/JetPack)<br /> <br /> Special Delivery Instructions (provide if needed):<br />
	     <textarea id="delivery_instructions" name="delivery_instructions" style="width: 50%; height: 100px;"></textarea>
	     <br /> <br /> <u>Please Confirm</u><br /> <input id="agree_package_properly" type="checkbox" name="agree_package_properly" value="agree_package_properly"> <span class="confirm_terms" style="color: black;">* Item must be packaged properly and a copy of the RMA included</span><br /> <input id="agree_payment_received" type="checkbox" name="agree_payment_received" value="agree_payment_received"> <span class="confirm_terms" style="color: black;">* Item will not be returned before payment is received</span><br /> <br />
	     <div id="agree_terms_message" style="display: none; color: red;">
		<i class="fa fa-check" aria-hidden="true"></i> Please check you've confirmed the items above, thank you!<br /> <br />
	     </div>
	     <div id="shipping_fields_required" style="display: none; color: red;">
		<i class="fa fa-exclamation-circle" aria-hidden="true"></i> Please finish entering your address for shipping
	     </div>
	     <div id="billing_fields_required" style="display: none; color: red;">
		<i class="fa fa-exclamation-circle" aria-hidden="true"></i> Please finish entering your contact details for billing contact
	     </div>
	     <br> <br />
	  </div>
       </div>
       <input type="hidden" name="form_submitted" value="submit" /> <input id="submit-form-button" type="button" value="Submit Return" onclick="submitForm()" class="btn" style="display: none;"><br />
 </form>
 </div>
</div>
</div>
<?php	} ?>

<!-- Event Log -->
<?php	if ($events) { ?>
       <div class="container">
          <div class="label">
             <h4>Events</h4>
          </div>
          <hr />
          <div class="table">
             <div class="tableHeading">
                <div class="tableCell">Event Date</div>
                <div class="tableCell">Person</div>
                <div class="tableCell">Description</div>
             </div>
             <?php
                 foreach ( $events as $event ) {
                  ?>
             <div class="tableRow">
                <div class="tableCell"><?=$event->date?></div>
                <div class="tableCell"><?=$event->person->full_name()?></div>
                <div class="tableCell"><?=$event->description?></div>
             </div>
             <?php
                }
                ?>
          </div>
       </div>
<?php	} ?>
</div>
