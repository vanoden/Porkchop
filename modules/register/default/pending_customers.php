<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
   .ui-autocomplete-loading {
        background: white url("https://jqueryui.com/resources/demos/autocomplete/images/ui-anim_basic_16x16.gif") right center no-repeat;
   }
</style>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>

    // check if the organization already exists for button states
    function checkExisting(id, orgName) {
        $.get("/_register/api?method=searchOrganizationsByName&term=" + orgName, function(data) {
            console.log(data.length);
            if (data.length > 0) {
                document.getElementById(id + "_assign_button").disabled = false;
                document.getElementById(id + "_new_button").disabled = true;
            } else {
                document.getElementById(id + "_assign_button").disabled = true;
                document.getElementById(id + "_new_button").disabled = false;
            }
        });
    };
    
    // page load apply button status, setup up autocomplete
    $(function() {
    
        // autocomplete textbox
        $(".organization").autocomplete({
            source: "/_register/api?method=searchOrganizationsByName",
            minLength: 2,
            select: function(event, ui) {
                var id = $(this)[0].id
                document.getElementById(id + "_assign_button").disabled = false;
                document.getElementById(id + "_new_button").disabled = true;
            }
        });

        // page load confirm if org exists already
        $(".organization").each(function(index) {
            checkExisting($(this)[0].id, $(this).val())
        });

        // if change the field, then keep the button disable sync'd
        $(".organization").keyup(function() {            
            checkExisting($(this)[0].id, $(this).val())
        });
    });
</script>
<style>
   .strong-text {
	font-weight: bold;
   }
   .small-text {
	font-size: 10px;
   }
   .cursor-pointer {
	cursor:pointer;
   }
   .hidden {
	display:none;
   }
   .vertical-align-top {
	vertical-align: unset;
   }   
   .resend-verify-message {
    font-size: 10px;
   }
</style>
<script>
   // reset the page forms to only allow the one in question
   function resetPage() {
	   $(".customer_status_form").hide();
	   $(".customer_status_form_links").show();
	   $(".customer_notes_form").hide();
	   $(".customer_notes_edit_links").show();
   }
   
   // edit status for pending customer
   function editStatus(queueId) {
	   resetPage();
	   $("#customer_status_form_" + queueId).show();
	   $("#customer_status_form_links_" + queueId).hide();       
   }
   
   // cancel edit status for pending customer
   function cancelEditStatus(queueId) {
	   resetPage();
	   $("#customer_status_form_" + queueId).hide();
	   $("#customer_status_form_links_" + queueId).show();       
   }
   
   // edit notes for pending customer
   function editNote(queueId) {
	   resetPage();
	   $("#customer_notes_form_" + queueId).show();
	   $("#customer_notes_edit_links_" + queueId).hide();       
   }
   
   // cancel edit notes for pending customer
   function cancelEditNote(queueId) {
	   resetPage();
	   $("#customer_notes_form_" + queueId).hide();
	   $("#customer_notes_edit_links_" + queueId).show();       
   }
   
   function assignCustomer(queueId) {
	   $("#customer_add_" + queueId).val('assignCustomer');
	   $("#customer_add_form_" + queueId).submit();
   }
   
   function denyCustomer(queueId) {
	   $("#customer_add_" + queueId).val('denyCustomer');
	   $("#customer_add_form_" + queueId).submit();
   }
   
   function resend(customerId) {
	  window.location.href = "/_register/pending_customers?verifyAgain="+customerId;
   }
   
   // date picker with max date being current day
   window.onload = function() {
	  $("#dateStart").datepicker({
		   onSelect: function(dateText, inst) {
			   var minDate = document.getElementById('min_date');
			   minDate.value = dateText;
		   }, 
		   maxDate: '0'
	   });
	  $("#dateEnd").datepicker({
		   onSelect: function(dateText, inst) {
			   var maxDate = document.getElementById('max_date');
			   maxDate.value = dateText;
		   }, 
		   maxDate: '0'
	   });
   }
</script>

<!-- Page Header -->
<?= $page->showTitle() ?>
<?=$page->showBreadcrumbs()?>
<?=$page->showMessages()?>
<!-- End Page Header -->

<form action="/_register/pending_customers" method="post" autocomplete="off">
  <div id="search_container">
    <div><label>Start </label><input type="text" id="dateStart" name="dateStart" placeholder="choose date" value="<?=isset($_REQUEST['fromDate']) ? $_REQUEST['fromDate'] : ''?>" /></div>
    <div><label>End </label><input type="text" id="dateEnd" name="dateEnd" placeholder="choose date" value="<?=isset($_REQUEST['toDate']) ? $_REQUEST['toDate'] : ''?>" /></div>
    <div>
      <?php foreach ($possibleStatii as $possibleStatus) { ?>
        <input type="checkbox" name="<?=$possibleStatus?>" value="<?=$possibleStatus?>"
        <?php
          if (isset($_REQUEST[$possibleStatus]) && $_REQUEST[$possibleStatus] == $possibleStatus) print " checked"; 
        ?> /><?=$possibleStatus?>
      <?php } ?>
    </div>
    <input type="submit" name="btn_submit" class="button" value="Filter" />
  </div>
</form>

<?php
  if ($page->success) {
?>
  <h3 class="success-message"><i class="fa fa-check-square-o" aria-hidden="true"></i> Customer Updated</h3>
<?php
  }
  if ($page->error) {
  ?>
  <h4 class="error-message"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error has occurred updating customer</h4>
<?php
}
?>

<h3>Pending Customers
   <?=isset($page->isSearchResults) ? "[Found Customers: ". count($queuedCustomersList)."]" : "";?>
</h3>
<!--	START First Table -->
<div class="tableBody">
  <div class="tableRowHeader">
    <div class="tableCell">Organization</div>
    <div class="tableCell">Customer</div>
    <div class="tableCell">Status</div>
    <div class="tableCell">Date</div>
    <div class="tableCell">Address</div>
    <div class="tableCell">Contact</div>
    <div class="tableCell">Product</div>
    <div class="tableCell">Admin Notes</div>
  </div>
  <?php
	  foreach ($queuedCustomersList as $queuedCustomer) {      
			$registerCustomer = $queuedCustomer->customer();  
			$productItem = new \Product\Item($queuedCustomer->product_id);
			$phone = $registerCustomer->contacts(array('type' => 'phone'))[0];
			$email = $registerCustomer->contacts(array('type' => 'email'))[0];
	?>
	<div class="tableRow">
		<div class="tableCell"><?=$queuedCustomer->name?>
      <?php	if ($queuedCustomer->is_reseller) { ?>&nbsp;[Reseller]<?php	} ?>
		  <form method="POST" id="customer_add_form_<?=$queuedCustomer->id?>" action="/_register/pending_customers?search=<?=$_REQUEST['search']?>">
		    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
        <?php
          switch ($queuedCustomer->status) {
          case 'PENDING':
        ?>
        <div>
          <label for="organization">Match Organization: </label>
          <input class="organization" id="organization_<?=$queuedCustomer->id?>" name="organization" value="<?=$queuedCustomer->name?>"/><br>
          <input type="image" class="icon-button" src="/img/icons/icon_cust_add-existing.svg" disabled="disabled" id="organization_<?=$queuedCustomer->id?>_assign_button" onclick="assignCustomer(<?=$queuedCustomer->id?>)" alt="Assign Existing" /> 
          <input type="image" class="icon-button" src="/img/icons/icon_cust_add-new.svg" disabled="disabled" id="organization_<?=$queuedCustomer->id?>_new_button" onclick="assignCustomer(<?=$queuedCustomer->id?>)" alt="Add as New" /> 
          <input type="image" class="icon-button" src="/img/icons/icon_cust_deny.svg" id="organization_<?=$queuedCustomer->id?>_deny_button" onclick="denyCustomer(<?=$queuedCustomer->id?>)" alt="Deny" />
        </div>
        <?php
          break;
          case 'VERIFYING':
        ?>
		    <span style="color: <?=colorCodeStatus("VERIFYING")?>"><i class="fa fa-clock-o" aria-hidden="true"></i> email validating
    	  <button type="button" class="resend-verify-message" onclick="resend(<?=$queuedCustomer->register_user_id?>)">Resend Verify Email Message</button></span>
        <?php
          break;
          case 'APPROVED':
        ?>
		    <span style="color: <?=colorCodeStatus("APPROVED")?>"><i class="fa fa-check-circle" aria-hidden="true"></i> approved</span>
        <?php
          break;
          default:
        ?>
		    <span style="color: <?=colorCodeStatus("DENIED")?>"><i class="fa fa-times" aria-hidden="true"></i> denied</span>
        <?php
          break;
          }
        ?>
		    <input id="customer_add_<?=$queuedCustomer->id?>" type="hidden" name="action" value="assignCustomer"/>
		    <input type="hidden" name="id" value="<?=$queuedCustomer->id?>"/>
		  </form>
		</div>
    
	  <div class="tableCell">
		  <?=$registerCustomer->first_name?> <?=$registerCustomer->last_name?>
		  <br/>
		  <strong>Login:</strong> <i><?=$registerCustomer->login?></i>
	  </div>
	  <div class="tableCell">
		  <div id="customer_status_form_<?=$queuedCustomer->id?>" class="hidden customer_status_form">
		    <form method="POST" action="/_register/pending_customers?search=<?=$_REQUEST['search']?>">
     		  <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">Update Status:<br/>
			    <select name="status">
				    <?php foreach ($possibleStatii as $possibleStatus) { ?>
				      <option value="<?=$possibleStatus?>" <?=($queuedCustomer->status == $possibleStatus) ? 'selected="selected"' : ""?>><?=$possibleStatus?></option>
				    <?php } ?>
			    </select>
			    <input type="hidden" name="action" value="updateStatus"/>
			    <input type="hidden" name="id" value="<?=$queuedCustomer->id?>"/>
			    <button type="submit">Save</button>
			    <button type="button" onclick="cancelEditStatus(<?=$queuedCustomer->id?>)">Cancel</button>
			  </form>
		  </div>
		  <div id="customer_status_form_links_<?=$queuedCustomer->id?>" class="customer_status_form_links">
			  <span style="color: <?=colorCodeStatus($queuedCustomer->status)?>"><?=$queuedCustomer->status?></span>
			  <a class="small-text cursor-pointer" onclick="editStatus(<?=$queuedCustomer->id?>)"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>
		  </div>
	  </div>
	  <div class="tableCell">
		  <?=date("F j, Y, g:i a", strtotime($queuedCustomer->date_created))?>
	  </div>
	  <div class="tableCell">
		  <?=$queuedCustomer->address?><br/>
		  <?=$queuedCustomer->city?>,
		  <?=$queuedCustomer->state?>
		  <?=$queuedCustomer->zip?>
	  </div>
	  <div class="tableCell">
		  <?php if (isset($phone->value)) { ?><strong>Phone:</strong> <?=$phone->value?><br/><?php } ?>
		  <?php if (isset($email->value)) { ?>
			  <strong>Email:</strong> <?=$email->value?><br/>
		  <?php } ?>
	  </div>
	  <div class="tableCell">
		  <?php if ($queuedCustomer->product_id) { ?>
			  <div style="color: #003308; padding: 10px 0px 10px 0px;"><?=$productItem->name?> [<?=$productItem->code?>]</div>
			  <strong>Serial #:</strong> <?=$queuedCustomer->serial_number?><br/>
      <?php } ?>
	  </div>
	  <div class="tableCell">
		  <div id="customer_notes_form_<?=$queuedCustomer->id?>" class="hidden customer_notes_form">
        <form method="POST" action="/_register/pending_customers?search=<?=$_REQUEST['search']?>">
          <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
          <input type="text" name="notes" value="<?=$queuedCustomer->notes?>"/><br/>
          <input type="hidden" name="action" value="updateNotes"/>
          <input type="hidden" name="id" value="<?=$queuedCustomer->id?>"/>
          <button type="submit">Save</button>
          <button type="button" onclick="cancelEditNote(<?=$queuedCustomer->id?>)">Cancel</button>
        </form>
		  </div>
		  <div id="customer_notes_edit_links_<?=$queuedCustomer->id?>" class="customer_notes_edit_links">
		    <?=$queuedCustomer->notes?> <br/><br/>
			  <?php if ($queuedCustomer->notes) { ?>
			    <a class="small-text cursor-pointer" onclick="editNote(<?=$queuedCustomer->id?>)"><i class="fa fa-pencil-square" aria-hidden="true"></i> Edit Note</a>
			  <?php } else { ?>
			    <a class="small-text cursor-pointer" onclick="editNote(<?=$queuedCustomer->id?>)"><i class="fa fa-plus-square" aria-hidden="true"></i> Add Note</a>
			  <?php } ?>
		  </div>
	  </div>
  </div>
  <?php	} ?>
</div>
<!--	END First Table -->
