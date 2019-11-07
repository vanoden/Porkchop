<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    * {
	    box-sizing: border-box;
    }

    .row {
	    display: -ms-flexbox;
	    /* IE10 */
	    display: flex;
	    -ms-flex-wrap: wrap;
	    /* IE10 */
	    flex-wrap: wrap;
	    margin: 0 -16px;
    }

    .col-25 {
	    -ms-flex: 25%;
	    /* IE10 */
	    flex: 25%;
    }

    .col-50 {
	    -ms-flex: 50%;
	    /* IE10 */
	    flex: 50%;
    }

    .col-75 {
	    -ms-flex: 75%;
	    /* IE10 */
	    flex: 75%;
    }

    .col-25,
    .col-50,
    .col-75 {
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

    @media (max-width: 800px) {
	    .row {
		    flex-direction: column-reverse;
	    }
	    .col-25 {
		    margin-bottom: 20px;
	    }
    }

    .tableBody,
    .tableTitle {
	    display: table;
	    width: 100%;
	    max-width: 800px;
	    border-color: #dedede;
	    border-style: solid;
    }

    .tableBody.half,
    .tableTitle.half {
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

    .stepwizard-step p {
	    margin-top: 10px;
    }

    .stepwizard-row {
	    display: table-row;
    }

    .stepwizard {
	    display: table;
	    width: 100%;
	    position: relative;
    }

    .stepwizard-step button[disabled] {
	    opacity: 0.5 !important;
	    filter: alpha(opacity=0.5) !important;
    }

    .stepwizard-row:before {
	    top: 14px;
	    bottom: 0;
	    position: absolute;
	    content: " ";
	    width: 100%;
	    height: 1px;
	    background-color: #ccc;
	    z-order: 0;
    }

    .stepwizard-step {
	    display: table-cell;
	    text-align: center;
	    position: relative;
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
</style>
<script type="text/javascript">

    // show the billing address form, step 2
    function showBilling() {
        document.getElementById('billing_address_form').style.display = 'block';
        document.getElementById('show-billing-button').style.display = 'none';
        document.getElementById('show-checklist-button').style.display = 'block';
    }

    // show step 3 terms of conditions for the return
    function showTerms() {
        document.getElementById('checklist_form').style.display = 'block';
        document.getElementById('show-checklist-button').style.display = 'none';
        document.getElementById('submit-form-button').style.display = 'block';
    }
    
    // toggle same as billing address
    function toggleBilling() {
        if (document.getElementById('billing_address_container').style.display == "block") {
            document.getElementById('billing_address_container').style.display = "none";
        } else {
            document.getElementById('billing_address_container').style.display = "block";
        }
    }
    
    // check if a shipping field is populated by id
    function checkForShippingValues(elementName) {
        if (!document.getElementById(elementName).value) shippingFieldsPopulated = false;
    }
    
    // check if a billing field is populated by id
    function checkForBillingValues(elementName) {
        if (!document.getElementById(elementName).value) billingFieldsPopulated = false;
    }
    
    // validate and submit form
    var shippingFieldsPopulated = true;
    var billingFieldsPopulated = true;
    function submitForm() {

        var shippingFields = ['shipping_firstname', 'shipping_address', 'shipping_city', 'shipping_state', 'shipping_zip'];
        shippingFieldsPopulated = true;
        shippingFields.forEach(function (element){checkForShippingValues(element)});

        var billingFields = ['billing_firstname', 'billing_address', 'billing_city', 'billing_state', 'billing_zip'];
        billingFieldsPopulated = true;
        billingFields.forEach(function (element){checkForBillingValues(element)});

        // show shipping fields are required
        var shippingFieldItems = Array.prototype.slice.call(document.getElementsByClassName("shipping_fields"));
        if (!shippingFieldsPopulated) {
            shippingFieldItems.forEach(function (element){updateFieldBackground(element, 'rgba(240, 173, 140, 0.60)')});
            document.getElementById("shipping_fields_required").style.display="block";
            return false;
        } else {
            shippingFieldItems.forEach(function (element){updateFieldBackground(element, '#f0f8ff')});
            document.getElementById("shipping_fields_required").style.display="none";
        }
        
        // show billing fields are required, if the they didn't check same as shipping
        var billingFieldItems = Array.prototype.slice.call(document.getElementsByClassName("billing_fields"));
        if (!billingFieldsPopulated && !document.getElementById("billing_same_as_shipping").checked) {
            billingFieldItems.forEach(function (element){updateFieldBackground(element, 'rgba(240, 173, 140, 0.60)')});
            document.getElementById("billing_fields_required").style.display="block";
            return false;
        } else {
            billingFieldItems.forEach(function (element){updateFieldBackground(element, '#f0f8ff')});
            document.getElementById("billing_fields_required").style.display="none";
        }
        
        // confirm terms of RMA are required
        var confirmTermsItems = Array.prototype.slice.call(document.getElementsByClassName("confirm_terms"));
        if (!document.getElementById("agree_package_properly").checked || !document.getElementById("agree_payment_received").checked) {
            document.getElementById("agree_terms_message").style.display="block";
            confirmTermsItems.forEach(function (element){updateMessageColors(element, 'red')});
            return false;
        } else {
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
</script>
<h1 style="display:inline;">Return Merchandise Authorization</h1>
<? if (!$rma->document()->exists()) { ?>
<span class="container">
    (<span class="label">Printable Document</span>
    <span class="value">&nbsp;<a href="/_storage/file?id=<?=$rma->document()->id?>"> <i class="fa fa-file"></i> View</a></span>)
</span>
<br/><br/>
<? } ?>
<div id="support_rma">
   <div class="container">
      <span class="label">Number</span>
      <span class="value"><?=$rmaNumber?></span>
   </div>
   <div class="container">
      <span class="label">Ticket</span>
      <span class="value"><a href="/_support/request_item/<?=$rmaItemId?>"><?=$rmaTicketNumber?></a></span>
   </div>
   <div class="container">
      <span class="label">Contact</span>
      <span class="value"><?=$rmaCustomerFullName?> - <?=$rmaCustomerOrganizationName?></span>
   </div>
   <div class="container">
      <span class="label">Approved By</span>
      <span class="value"><?=$rmaApprovedByName?></span>
   </div>
   <div class="container">
      <span class="label">Date Approved</span>
      <span class="value"><?=$rmaDateApproved?></span>
   </div>
   <div class="container">
      <span class="label">Status</span>
      <span class="value"><?=$rmaStatus?></span>
   </div>
   <div class="container">
      <span class="label">Product</span>
      <span class="value"><?=$rmaProductCode?> - <?=$rmaSerialNumber?></span>
   </div>
   <div class="stepwizard">
      <div class="stepwizard-row">
         <div class="stepwizard-step">
            <a type="button" href="#shipping" class="btn btn-default btn-circle">1</a>
            <p>Return Address</p>
         </div>
         <div class="stepwizard-step">
            <a type="button" href="#billing" class="btn btn-primary btn-circle">2</a>
            <p>Billing Address</p>
         </div>
         <div class="stepwizard-step">
            <a type="button" href="#terms" class="btn btn-default btn-circle">3</a>
            <p>Terms and Conditions</p>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-75">
         <div class="container">
            <form method="post" id="submit_rma_form">
               <div class="row">
                  <div id="shipping_address_form" class="col-50">
                     <a name="shipping"><h2>Return Shipping Address</h2></a>
                     <label for="fname"><i class="fa fa-user"></i> Full Name</label>
                     <input type="text" id="shipping_firstname" name="shipping_firstname" class="shipping_fields" placeholder="John M. Doe" value="<?=$rmaCustomerFullName?>">
                     <label for="adr"><i class="fa fa-address-card-o"></i> Address</label>
                     <input type="text" id="shipping_address" name="shipping_address" class="shipping_fields" placeholder="542 W. 15th Street">
                     <input type="text" id="shipping_address2" name="shipping_address2" class="shipping_fields" placeholder="Suite 1">
                     <label for="city"><i class="fa fa-institution"></i> City</label>
                     <input type="text" id="shipping_city" name="shipping_city" class="shipping_fields" placeholder="New York">
                     <div class="row">
                        <div class="col-50">
                           <label for="state">State</label>
                           <input type="text" id="shipping_state" name="shipping_state" class="shipping_fields" placeholder="NY">
                        </div>
                        <div class="col-50">
                           <label for="zip">Zip</label>
                           <input type="text" id="shipping_zip" name="shipping_zip" class="shipping_fields" placeholder="10001">
                        </div>
                     </div>
                  </div>
               </div>
               <input id="show-billing-button" onclick="showBilling()" type="button" value="Next" class="btn" style="display:block;"><br/>
               <div id="billing_address_form" class="row" style="display:none;">
                  <div class="col-50">
                     <a name="billing"><h2>Billing Address</h2></a>
                     <input type="checkbox" id="billing_same_as_shipping" name="billing_same_as_shipping" value="billing_same_as_shipping" onclick="toggleBilling()"> Same as Shipping<br/><br/>
                     <div id="billing_address_container" style="display:block;">
                         <label for="fname"><i class="fa fa-user"></i> Full Name</label>
                         <input type="text" id="billing_firstname" name="billing_firstname" class="billing_fields" placeholder="John M. Doe" value="<?=$rmaCustomerFullName?>">
                         <label for="adr"><i class="fa fa-address-card-o"></i> Address</label>
                         <input type="text" id="billing_address" name="billing_address" class="billing_fields" placeholder="542 W. 15th Street">
                         <input type="text" id="billing_address2" name="billing_address2" class="billing_fields" placeholder="Suite 1">
                         <label for="city"><i class="fa fa-institution"></i> City</label>
                         <input type="text" id="billing_city" name="billing_city" class="billing_fields" placeholder="New York">
                         <div class="row">
                            <div class="col-50">
                               <label for="state">State</label>
                               <input type="text" id="billing_state" name="billing_state" class="billing_fields" placeholder="NY">
                            </div>
                            <div class="col-50">
                               <label for="zip">Zip</label>
                               <input type="text" id="billing_zip" name="billing_zip" class="billing_fields" placeholder="10001">
                            </div>
                         </div>
                     </div>
                  </div>
               </div>
               <input id="show-checklist-button" onclick="showTerms()" type="button" value="Next" class="btn" style="display:none;"><br/>
               <div id="checklist_form" class="row" style="display:none;">   
                  <div class="col-50">
                     <a name="terms"><h3><u>Items Checklist</u></h3></a>
                     * Only the specified item may be returned. Other contents may be discarded<br/><br/>
                     <input type="checkbox" name="power_cord" value="power_cord"> Power Cord<br/>
                     <input type="checkbox" name="filters" value="filters"> Filters<br/>
                     <input type="checkbox" name="battery" value="battery"> Battery<br/>
                     <input type="checkbox" name="carry_bag" value="carry_bag"> Carry Bag<br/>
                     <input type="checkbox" name="usb_comm_cable" value="usb_comm_cable"> USB Comm Cable<br/>
                     <input type="checkbox" name="cellular_access_point" value="cellular_access_point"> Cellular Access Point (MiFi/JetPack)<br/><br/>
                     
                     Special Delivery Instructions (provide if needed):<br/>
                     <textarea name="delivery_instructions" style="width: 50%; height: 100px;"></textarea><br/>
                     
                     Tracking Number(s) [optional]:<br/>
                     <textarea name="tracking_numbers" style="width: 50%; height: 100px;"></textarea><br/><br/>
                     
                     <u>Please Confirm</u><br/>
                     <input id="agree_package_properly" type="checkbox" name="agree_package_properly" value="agree_package_properly"> <span class="confirm_terms" style="color:black;">* Item must be packaged properly and a copy of the RMA included</span><br/>
                     <input id="agree_payment_received" type="checkbox" name="agree_payment_received" value="agree_payment_received"> <span class="confirm_terms" style="color:black;">* Item will not be returned before payment is received</span><br/><br/>
                     
                     <div id="agree_terms_message" style="display:none; color:red;"><i class="fa fa-check" aria-hidden="true"></i> Please check you've confirmed the items above, thank you!<br/><br/></div>
                     <div id="shipping_fields_required" style="display:none; color:red;"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Please finish entering your address for shipping</div>
                     <div id="billing_fields_required" style="display:none; color:red;"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Please finish entering your address for billing</div>
                     <br><br/>
                  </div>
               </div>
               <input type="hidden" name="form_submitted" value="submit"/>
               <input id="submit-form-button" type="button" value="Submit Return" onclick="submitForm()" class="btn" style="display:none;"><br/>
            </form>
         </div>
      </div>
   </div>
   <br/>
   <div class="container">
      <div class="label">
         <h4>Events</h4>
      </div>
      <hr/>
      <div class="table">
         <div class="tableHeading">
            <div class="tableCell">Event Date</div>
            <div class="tableCell">Person</div>
            <div class="tableCell">Description</div>
         </div>
         <?php	
            if ($events) {
                foreach ($events as $event) {
            ?>
             <div class="tableRow">
                <div class="tableCell"><?=$event->date?></div>
                <div class="tableCell"><?=$event->person->full_name()?></div>
                <div class="tableCell"><?=$event->description?></div>
             </div>
         <?php
                }
            } ?>
      </div>
   </div>
   <? if (!$rma->document()->exists()) { ?>
       <br/><br/><br/><br/>
       <span class="container" style="float:right;">
       (<span class="label">Printable Document</span>
       <span class="value">&nbsp;<a href="/_storage/file?id=<?=$rma->document()->id?>"> <i class="fa fa-file"></i> View</a></span>)
       </span><br/><br/>
   <? } ?>
</div>
