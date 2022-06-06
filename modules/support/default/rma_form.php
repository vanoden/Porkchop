<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="/js/geography.js" type="text/javascript"></script>
<script type="text/javascript">
	// show the billing address form, step 2
	function showBilling() {
		document.getElementById('billing_contact_form').style.display = 'block';
		document.getElementById('show-billing-button').style.display = 'none';
		document.getElementById('show-checklist-button').style.display = 'flex';
	}
   
	// show step 3 terms of conditions for the return
	function showTerms() {
		document.getElementById('checklist_form').style.display = 'block';
		document.getElementById('show-checklist-button').style.display = 'none';
		document.getElementById('submit-form-button').style.display = 'flex';
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
			shippingFieldItems.forEach(function (element){updateFieldBackground(element, '#fff')});
			document.getElementById("shipping_fields_required").style.display="none";
		}
		else {
			shippingFieldItems.forEach(function (element){updateFieldBackground(element, '#ed1c24')});
			document.getElementById("form-error").style.display="block";
			document.getElementById("shipping_fields_required").style.display="flex";
			return false;
		}
       
		// show billing fields are required, if the they didn't check same as shipping
		var billingFieldItems = Array.prototype.slice.call(document.getElementsByClassName("billing_fields"));
		if (checkFieldsArray(billingFields) || document.getElementById('billing_contact_picker').value > 0) {
			billingFieldItems.forEach(function (element){ updateFieldBackground(element, '#f0f8ff'); });
			document.getElementById("billing_fields_required").style.display="none";
		}
		else {
			billingFieldItems.forEach(function (element){ updateFieldBackground(element, '#ed1c24'); });
			document.getElementById("form-error").style.display="block";
			document.getElementById("billing_fields_required").style.display="flex";
			return false;
		}

		// confirm terms of RMA are required
		var confirmTermsItems = Array.prototype.slice.call(document.getElementsByClassName("confirm_terms"));
		if (!document.getElementById("agree_package_properly").checked || !document.getElementById("agree_payment_received").checked) {
			document.getElementById("form-error").style.display="block";
			document.getElementById("agree_terms_message").style.display="flex";
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
		element.style.outlineColor = color;
		element.style.outlineWidth = "2px";
		element.style.outlineStyle = "solid";
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
	function changeCountry() {
		var countryDropdown = document.getElementById('shipping_country');
		var provinceDropdown = document.getElementById('shipping_province');
		document.getElementById('shipping_address_city').style.display = "hidden";
		document.getElementById('shipping_address_zip').style.display = "hidden";
		var provinceList = Object.create(ProvinceList);
		var countryId = countryDropdown.value;
		var provinces = provinceList.find({country_id:countryId});
		console.log(provinces);
		document.getElementById('shipping_province_container').style.display = "block";
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
	function changeProvince() {
		document.getElementById('shipping_address_city').style.display = "block";
		document.getElementById('shipping_address_zip').style.display = "block";
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
			// Show the Add New Shipping Info Container
				document.getElementById('add_new_shipping_address').style.height = "auto";
				document.getElementById('add_new_shipping_address').style.visibility = "visible";
				document.getElementById('add_new_shipping_address').style.overflow = "auto";
			var x = window.matchMedia("(max-width: 750px)")
			if(x.matches) {
				document.getElementById('add_new_shipping_address').style.display = "block";
			} else {
				document.getElementById('add_new_shipping_address').style.display = "grid";
			}
				
			
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
			document.getElementById('show-billing-button').style.display = "flex";
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
			document.getElementById('add_new_billing_contact').style.display = "flex";
		}
		if (document.getElementById('billing_contact_picker').value == '') {
			document.getElementById('show-checklist-button').style.display = "none";
			document.getElementById('add_new_billing_contact').style.display = "none";
			document.getElementById('show-billing-button').style.display = "none";
			document.getElementById('checklist_form').style.display = "none";
			document.getElementById('submit-form-button').style.display = "none";
		}
		else {
			document.getElementById('show-checklist-button').style.display = "flex";
		}
	}
    
    // radio button checks to hide location name for 'personal address'
	document.addEventListener("DOMContentLoaded", function (event) {
		if (document.querySelector('input[type=radio][name=shipping_address_type]')) {
			document.querySelector('input[type=radio][name=shipping_address_type]').addEventListener("change",function() {
				if (this.value == 'personal') {
					document.getElementById('shipping-radio-container').style.display = "none";
				} else {
					document.getElementById('shipping-radio-container').style.display = "flex";
				}
			});
		}
    });
</script>

<h2>Support</h2>
<nav id="breadcrumb">
	<ul>
		<li><a href="/_support/tickets">All Tickets</a></li>
		<li><a href="<?=$ticketLink?>" class="value">Ticket# <?=$rmaTicketNumber?></a></li>
		<li><?=$rmaNumber?></li>
	</ul>
</nav>

<?php	if ($page->errorCount() > 0) { ?>
<section>
<ul class="connectBorder errorText">
	<li><?=$page->errorString()?></li>
</ul>
<section>
<?php	} else if (!empty($page->success)) {?>
<section>
<ul class="connectBorder progressText">
	<li><?=$page->success?></li>
</ul>
<section>
<?php	} ?>
<?php if (empty($page->success)) {?>
<section id="form-message">
	<ul class="connectBorder infoText">
		<li><?=$rmaMessage?></li>
	</ul>
</section>
<?php	} ?>

<!-- Form Messaging -->
<section id="support_rma">
	<?php	if ($rmaReceived) { ?>
	<ul class="form-grid three-col">
		<li><label for="">Date</label><?=$shippingPackage->date_received?></li>
		<li><label for="">Received by</label><?=$shippingPackage->user_received()->full_name()?></li>
		<?php	if ($rmaSubmitted && ! $rmaReceived) { ?>
			<li><label for="">Please include the following form with your return:</label> <a href="/_support/rma_pdf/<?=$rmaCode?>" target="_blank">Download RMA Document</a></li>
		<?php	} else if ($rmaSubmitted) { ?>
			<li><label for="">Reprint RMA Document:</label> <a href="/_support/rma_pdf/<?=$rmaCode?>" target="_blank">Download RMA Document</a></li>
		<?php	} ?>
		<?php	if ($rmaSubmitted) { ?>
			<li><label for="">Sending From:</label> <?=$sentFromLocation->address_1?><br><?=$sentFromLocation->address_2?><br>
			<?=$sentFromLocation->city?>, <?=$sentFromLocation->zip_code?><br><i style="font-size: .8rem;">*Notes: <?=$sentFromLocation->notes?></i>
			<li><label for="">Shipping To:</label> <?=$sentToLocation->address_1?><br><?=$sentToLocation->address_2?><br>
			<?=$sentToLocation->city?>, <?=$sentToLocation->zip_code?><br><i style="font-size: .8rem;">*Notes: <?=$sentToLocation->notes?></i>
		<?php	} ?>
	</ul>
	<?php	} ?>
</section>

<!-- Package Received -->
<?php	if (!empty($shippingShipment->id)) { ?>
	<h3 class="eyebrow">Current Package Info:</h3>
	<ul class="form-grid three-col">
		<li>
			<label for="vendor">Vendor:</label>
			<?=$shippingShipment->vendor()->name?>
		</li>
		<li>
			<label for="tracking-number">Tracking #:</label>
			<?=$shippingPackage->tracking_code;?>
		</li>
	</ul>
<?php	} ?>

<!-- Receipt Info -->
<?php	if ($GLOBALS['_SESSION_']->customer->can('receive shipments') && $rmaSubmitted && !$rmaReceived) { ?>
	<form method="post" id="submit_package_details">
		<h3 class="eyebrow">Receipt Info:</h3>
		<input type="hidden" name="id" value="<?=$rma->id?>" /><!-- What does this do -->
		<ul class="form-grid three-col">
			<li>
				<label for="dateReceived">Date Received</label>
				<input id="rma_date_received" type="text" name="date_received" class="value input" value="<?=date('Y-m-d H:i:s')?>"/></li>
			<li>
				<label for="dateReceived">Condition of Package</label>
				<select id="rma_condition_received" class="value input" style="display: flex" name="condition">
					<option value="OK">OK</option>
					<option value="DAMAGED">Damaged</option>
				</select>
			</li>
		</ul>
		<input type="submit" name="form_submitted" class="button" value="Receive Package" />
	</form>
<?php	} ?>

<!-- Ticket Info -->
<section>
<h3 class="eyebrow"><?=$rmaNumber?></h3>
			<ul class="form-grid three-col">
				<li><label for="">Contact</label><?=$rmaCustomerFullName?></li>
				<li><label for="">Organization</label><?=$rmaCustomerOrganizationName?></li>
				<li><label for="">Approved by</label><?=$rmaApprovedByName?></li>
				<li><label for="">Date</label><?=$rmaDateApproved?></li>
				<li><label for="">Status</label><?=$rmaStatus?></li>
				<li><label for="">Product</label><a href="<?=$productLink?>"><?=$rmaProduct->code?>, Serial#: <?=$rmaSerialNumber?></a></li>
			<ul>
</section>

<!-- Package Form -->
<section>
	<?php	if ($showShippingForm) { ?>
	<h3 class="eyebrow"><?=empty($shippingPackage->id) ? "Add" : "Update"?> your shipment info:</h3>
	<form method="post" id="submit_package_details">
		<ul class="form-grid three-col">
			<li>
				<label for="tracking_code">Tracking Number</label> 
				<input type="text" id="tracking_code" name="tracking_code" class="tracking_code" placeholder="1Z9999999999999999" value="<?=$shippingPackage->tracking_code;?>">
			</li>
			<li>
				<label for="vendor_id">Shipping Vendor</label> 
				<select id="vendor_id" name="vendor_id" class="tracking_code" placeholder="10">
					<option value="">Select</option>
					<?php		foreach ($shippingVendors as $shippingVendor) { ?>
					<option value="<?=$shippingVendor->id?>"<?php if ($shipment->vendor_id == $shippingVendor->id) print " selected";?>><?=$shippingVendor->name?></option>
					<?php		} ?>
				</select>
			</li>
		</ul>
		<input type="hidden" name="form_submitted" value="package_details_submitted" />
		<input id="add-package-details" type="submit" value="<?=empty($shippingPackage->id) ? "Add" : "Update"?> Package Details" class="btn">
	</form>
	<?php	} ?>
</section>


<!-- ========== SHIPPING ADDRESS ========== -->
<?php	if (! $rmaSubmitted) { ?>
	<form method="post" id="submit_rma_form">
		<section class="form-group" id="shipping_address_form">
			<h3 class="eyebrow">Shipping Info</h3>
			<ul>
				<li>
				<label for="">Shipping from location:</label>
				<select id="shipping_address_picker" name="shipping_address_picker" onchange="selectShippingAddress()">
					<option value="">--</option>
					<option value="0">[add new]</option>
					<?php foreach ($customerLocations as $customerLocation) { ?>
					<option value="<?=$customerLocation->id?>"><?=$customerLocation->name?> <?=$customerLocation->address_1?> <?=$customerLocation->address_2?>
					<?=$customerLocation->city?> <?=$customerLocation->zip_code?></option>
					<?php } ?>
				</select>
				</li>
			</ul>

			<ul id="add_new_shipping_address" class="form-grid four-col connectBorder" style="height: 0; overflow: hidden; visibility: hidden;">
				<h4>Add New Shipping Info</h4>
				<li class="form-selectors"><input type="radio" name="shipping_address_type" value="business" checked="checked"><label for="shipping_address_type">Business</label></li>
				<li class="form-selectors"><input type="radio" name="shipping_address_type" value="personal"><label for="shipping_address_type">Personal</label></li>
				<li><label for="fname">Location Name</label><input type="text" id="shipping_location_name" name="shipping_location_name" placeholder="Nickname"></li>
				<li><label for="shipping_address">Address</label><input type="text" id="shipping_address" name="shipping_address" class="shipping_fields" placeholder="Street Address"></li>
				<li><label for="shipping_address">Apt/Suite/P.O.</label><input type="text" id="shipping_address2" name="shipping_address2" placeholder="Apt/Suite">
				</li>
				<li>
				<label for="">Country:</label>
				<select id="shipping_country" name="shipping_country" onchange="changeCountry()">
					<option value="0">-</option>
					<?php foreach ($allCountriesList as $country) {	?>
						<option value="<?=$country->id?>"><?=$country->name?></option>
					<?php	}	?>
				</select>
				</li>
				<li id="shipping_province_container">
					<label for="shipping_province">State/Province</label>
					<select id="shipping_province" name="shipping_province" onchange="changeProvince()" style="min-width: 210px;">
						<option value="0">-</option>
					</select>
				</li>
				<li id="shipping_address_city" style="display: none;"><label for="city">City</label><input type="text" id="shipping_city" name="shipping_city" class="shipping_fields"></li>
				<li id="shipping_address_zip" style="display: none;"><label for="zip">Zip</label> <input type="text" id="shipping_zip" name="shipping_zip" class="shipping_fields">
				</li>
			</ul>
		</section>

		<input id="show-billing-button" onclick="showBilling()" type="button" value="Next" class="btn" style="display:none;">

<!-- ========== BILLING ADDRESS ========== -->
		<section class="form-group" id="billing_contact_form" style="display:none;">
			<h3 class="eyebrow">Billing Contact</h3>
			<a name="billing"></a>
			<ul>
				<li>
					<label for="">Select user:</label>
					<select id="billing_contact_picker" name="billing_contact_picker" onchange="selectBillingContact()">
						<option value="">--</option>
						<option value="0">[add new]</option>
						<?php foreach ($organizationUsers as $organizationUser) { ?>
						<option value="<?=$organizationUser->id?>"<?php if ($organizationUser->id == $GLOBALS['_SESSION_']->customer->id) print " selected"; ?>><?=$organizationUser->first_name?> <?=$organizationUser->last_name?> </option>
						<?php } ?>
					</select>
				</li>
			</ul>
		</section>
		<section class="form-group" id="add_new_billing_contact" style="display:none;">
			<ul class="form-grid four-col connectBorder">
			<h4>Add New Billing Contact</h4>
			<li><label for="fname">First Name</label><input type="text" id="billing_firstname" name="billing_firstname" class="billing_fields"></li>
			<li><label for="fname">Last Name</label><input type="text" id="billing_lastname" name="billing_lastname" class="billing_fields"></li>
			<li><label for="fname">Email</label><input type="email" id="billing_email" name="billing_email" class="billing_fields" placeholder="user@email.com" pattern=".+@globex\.com" size="30"></li>
			<li><label for="fname">Phone</label><input type="tel" id="billing_phone" name="billing_phone" class="billing_fields" placeholder="123-555-5555" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}">
			</ul>
		</section>

		<input id="show-checklist-button" onclick="showTerms()" type="button" value="Next" class="btn" style="display:none;">

		<section class="form-group" id="checklist_form" style="display:none;">
			<a name="terms"></a>
			<h3 class="eyebrow">Included Items</h3>
			<ul class="form-grid three-col connectBorder">
				<h4>Check boxes to confirm all applicable items are included*</h4>
				<p>* Only the specified item may be returned. Other contents may be discarded</p>
				<li class="form-selectors"><input type="checkbox" name="power_cord" value="power_cord"><label for="power_cord">Power Cord</label></li>
				<li class="form-selectors"><input type="checkbox" name="filters" value="filters"><label for="filters">Filters</label></li>
				<li class="form-selectors"><input type="checkbox" name="battery" value="battery"><label for="battery">Battery</label></li>
				<li class="form-selectors"><input type="checkbox" name="carry_bag" value="carry_bag"><label for="carry_bag">Carry Bag</label></li>
				<li class="form-selectors"><input type="checkbox" name="usb_comm_cable" value="usb_comm_cable"><label for="usb_comm_cable">USB Comm Cable</label></li>
				<li class="form-selectors"><input type="checkbox" name="cellular_access_point" value="cellular_access_point"><label for="cellular_access_point">Cellular Access Point (MiFi/JetPack)</label></li>
				<div>
					<li><label for="delivery_instructions">Special Delivery Instructions (provide if needed):</label></li>
					<li><textarea id="delivery_instructions" name="delivery_instructions"></textarea></li>
				</div>
				<h4 class="eyebrow">Please check the boxes below to accept the terms:</h4>
				<li class="form-selectors"><input id="agree_package_properly" type="checkbox" name="agree_package_properly" value="agree_package_properly">
				<label for="agree_package_properly">Item must be packaged properly and a copy of the RMA included</label></li>
				<li class="form-selectors"><input id="agree_payment_received" type="checkbox" name="agree_payment_received" value="agree_payment_received">
				<label for="agree_payment_received">Item will not be returned before payment is received</label></li>
			</ul>
		</section>

		<section id="form-error" style="display: none;">
			<ul class="connectBorder errorText">
				<li id="agree_terms_message" class="error-text" style="display: none;">Please check you've confirmed the items above, thank you!</li>
				<li id="shipping_fields_required" class="error-text" style="display: none;">Please finish entering your address for shipping</li>
				<li id="billing_fields_required" class="error-text" style="display: none;">Please finish entering your contact details for billing contact</li>
			</ul>
		</section>

		<input type="hidden" name="form_submitted" value="submit" />
		<input id="submit-form-button" type="button" class="btn" value="Submit Return" onclick="submitForm()" style="display:none;" />
	</form>
<?php	} ?>

<!-- Event Log -->
<?php	if ($events) { ?>
	<div class="container">
		<div class="label">
				<h4>Events</h4>
		</div>
		<hr />
		<div class="tableBody bandedRows">
				<div class="tableRowHeader">
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