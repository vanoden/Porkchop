<!-- @TODO move this to the main site html.src module -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
   .ui-autocomplete-loading {
        background: white url("https://jqueryui.com/resources/demos/autocomplete/images/ui-anim_basic_16x16.gif") right center no-repeat;
   }
</style>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
   $( function() {
	 $( ".organization" ).autocomplete({
	   source: "/_register/api?method=searchOrganizationsByName",       
	   minLength: 2,
	   select: function( event, ui ) {}
	 });
   } );
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
   .success-message {
	   border: 1px solid #0093ff;
	   background-color: #0093ff;
	   padding: 5px;
	   border-radius: 5px;
	   color: white;
   }
   .error-message {
	   border: 1px solid #ef2929;
	   background-color: #ef2929;
	   padding: 5px;
	   border-radius: 5px;
	   color: white;
   }
   td, tr, th {
	   border:0;
   }
   table {
	border-bottom: 1px solid #000;
   }
   .vertical-align-top {
	vertical-align: unset;
   }
</style>
<script>

   // reset the page forms to only allow the one in question
   function resetPage() {
	   $(".customer_registration_form").hide();
	   $(".customer_registration_form_links").show();
	   $(".registration_notes_form").hide();
	   $(".registration_notes_edit_links").show();
   }
   
   // edit status for pending warranty registration
   function editStatus(queueId) {
	   resetPage();
	   $("#customer_registration_form_" + queueId).show();
	   $("#customer_registration_form_links_" + queueId).hide();       
   }

   // cancel edit status for pending warranty registration
   function cancelEditStatus(queueId) {
	   resetPage();
	   $("#customer_registration_form_" + queueId).hide();
	   $("#customer_registration_form_links_" + queueId).show();       
   }
   
   // edit notes for pending warranty registration
   function editNote(queueId) {
	   resetPage();
	   $("#registration_notes_form_" + queueId).show();
	   $("#registration_notes_edit_links_" + queueId).hide();       
   }
   
   // cancel edit notes for pending warranty registration
   function cancelEditNote(queueId) {
	   resetPage();
	   $("#registration_notes_form_" + queueId).hide();
	   $("#registration_notes_edit_links_" + queueId).show();       
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
<div class="breadcrumbs">
   <a href="/_support/requests">Support Home</a> &gt; Pending Registrations
</div>
<h2 style="display: inline-block;"><i class="fa fa-id-badge" aria-hidden="true"></i> Customer Product Registrations </h2>
<?php include(MODULES.'/support/partials/search_bar.php'); ?>
<div style="width: 100%;">
   <?	if ($page->errorCount()) { ?>
       <div class="form_error"><?=$page->errorString()?></div>
   <?	} ?>
   <form action="/_support/pending_registrations" method="post" autocomplete="off">
	  <input id="min_date" type="hidden" name="min_date" readonly value="<?=$_REQUEST['min_date']?>" />
	  <input id="max_date" type="hidden" name="min_date" readonly value="<?=$_REQUEST['max_date']?>" />
	  <table>
		 <tr>
			<th><span class="label"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> Purchased Date Start</th>
			<th><span class="label"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> Purchased Date End</th>
			<th><span class="label"><i class="fa fa-filter" aria-hidden="true"></i> Status</span></th>
		 </tr>
		 <tr>
			<td><input type="text" id="dateStart" name="dateStart" class="value input" value="<?=$_REQUEST['dateStart']?>" /></td>
			<td><input type="text" id="dateEnd" name="dateEnd" class="value input" value="<?=$_REQUEST['dateEnd']?>" /></td>
			<td style="width: 50%;">
			   <?php foreach ($registrationQueueList->possibleStatus as $possibleStatus) { ?>
			   <input type="checkbox" name="<?=$possibleStatus?>" value="<?=$possibleStatus?>"
				  <?php
					 if ($_REQUEST[$possibleStatus] == $possibleStatus) print " checked"; 
					 ?> /><?=$possibleStatus?>
			   <?php } ?>
			</td>
		 </tr>
	  </table>
	  <input type="submit" name="btn_submit" class="button" value="Filter Results" style="float:right;" /><br/><br/>
   </form>
</div>
<?php
   if ($page->success) {
   ?>
<h3 class="success-message"><i class="fa fa-check-square-o" aria-hidden="true"></i> Warranty Updated</h3>
<?php
   }
   if ($page->error) {
   ?>
<h4 class="error-message"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error has occurred updating warranty</h4>
<?php
   }
   ?>
<h2 style="display: inline-block;">Pending Product Registrations
   <?=($page->isSearchResults)? "[Found Customers: ". count($registrationQueueList)."]" : "";?>
</h2>

<!--	START First Table -->
<div class="tableBody" style="min-width: 100%;">
   <div class="tableRowHeader">
      <div class="tableCell">Entry Date</div>
      <div class="tableCell">Product / Serial Number</div>
	  <div class="tableCell">Customer</div>
	  <div class="tableCell">Date Purchased</div>
	  <div class="tableCell">Status</div>
	  <div class="tableCell">Distributor</div>
	  <div class="tableCell">Admin Notes</div>
   </div>
   <?php
	  foreach ($queuedProductRegistration as $queuedRegistration) {
	        $customer = new \Register\Person($queuedRegistration->customer_id);		
			$productItem = new \Product\Item($queuedRegistration->product_id);
	  ?>
	<div class="tableRow">
	  <div class="tableCell">
		    <?=date("F j, Y", strtotime($queuedRegistration->date_created))?><br/>
	  </div>
	  <div class="tableCell">
	  	 <?php if ($queuedRegistration->product_id) { ?>
			 <div style="color: #003308; padding: 10px 0px 10px 0px;"><?=$productItem->name?> [<?=$productItem->code?>]</div>
			 <strong>Serial #:</strong> <?=$queuedRegistration->serial_number?><br/>
		 <?php } ?>
		 <br/>
	  </div>
	  <div class="tableCell">
  	    <?=$customer->first_name?>
        <?=$customer->last_name?><br/>
        <i><?=$customer->organization->name?></i>
	  </div>
	  <div class="tableCell">
        <?=date("F j, Y", strtotime($queuedRegistration->date_purchased))?><br/>
	  </div>
	  <div class="tableCell">
	  	<div id="customer_registration_form_<?=$queuedRegistration->id?>" class="hidden customer_registration_form">
			<form method="POST" action="/_support/pending_registrations?search=<?=$_REQUEST['search']?>">
			   Update Status:<br/>
			   <select name="status">
				  <?php foreach ($registrationQueueList->possibleStatus as $possibleStatus) { ?>
				  <option value="<?=$possibleStatus?>" <?=($queuedRegistration->status == $possibleStatus) ? 'selected="selected"' : ""?>><?=$possibleStatus?></option>
				  <?php } ?>
			   </select>
			   <br/>
			   <input type="hidden" name="action" value="updateStatus"/>
			   <input type="hidden" name="id" value="<?=$queuedRegistration->id?>"/>
			   <button type="submit">Save</button>
			   <button type="button" onclick="cancelEditStatus(<?=$queuedRegistration->id?>)">Cancel</button>
			</form>
		 </div>
		 <div id="customer_registration_form_links_<?=$queuedRegistration->id?>" class="customer_registration_form_links">		 
			<br/>   
			<span style="color: <?=colorCodeStatus($queuedRegistration->status)?>">
			<?=$queuedRegistration->status?>
			</span>
			<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a class="small-text cursor-pointer" onclick="editStatus(<?=$queuedRegistration->id?>)"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>
			<br/><br/>
		 </div>
	  </div>
	  <div class="tableCell">
        <i><?=$queuedRegistration->distributor_name?></i>
	  </div>
	  <div class="tableCell">
		 <div id="registration_notes_form_<?=$queuedRegistration->id?>" class="hidden registration_notes_form">
			<form method="POST" action="/_support/pending_registrations?search=<?=$_REQUEST['search']?>">
			   <input type="text" name="notes" value="<?=$queuedRegistration->notes?>"/><br/>
			   <input type="hidden" name="action" value="updateNotes"/>
			   <input type="hidden" name="id" value="<?=$queuedRegistration->id?>"/>
			   <button type="submit">Save</button>
			   <button type="button" onclick="cancelEditNote(<?=$queuedRegistration->id?>)">Cancel</button>
			</form>
		 </div>
		 <div id="registration_notes_edit_links_<?=$queuedRegistration->id?>" class="registration_notes_edit_links">
			<?=$queuedRegistration->notes?> <br/><br/>
			<?php if ($queuedRegistration->notes) { ?>
			<a class="small-text cursor-pointer" onclick="editNote(<?=$queuedRegistration->id?>)"><i class="fa fa-pencil-square" aria-hidden="true"></i> Edit Note</a>
			<?php } else { ?>
			<a class="small-text cursor-pointer" onclick="editNote(<?=$queuedRegistration->id?>)"><i class="fa fa-plus-square" aria-hidden="true"></i> Add Note</a>
			<?php } ?>
		 </div>
	  </div>
   </div>
   <?php	} ?>
</div>
<!--	END First Table -->
