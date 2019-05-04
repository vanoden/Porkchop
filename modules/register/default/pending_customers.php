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
        $("#customer_assign_" + queueId).val('assignCustomer');
        $("#customer_assign_form_" + queueId).submit();
    }
    
    function denyCustomer(queueId) {
        $("#customer_assign_" + queueId).val('denyCustomer');
        $("#customer_assign_form_" + queueId).submit();
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
	<a href="/_support/requests">Support Home</a> &gt; Customer Requests
</div>

<h2 style="display: inline-block;"><i class='fa fa-users' aria-hidden='true'></i> Customer Registrations </h2>
<?php include(MODULES.'/register/partials/search_bar.php'); ?>

<div style="width: 100%;">
   <?	if ($page->errorCount()) { ?>
       <div class="form_error"><?=$page->errorString()?></div>
   <?	} ?>
   <form action="/_register/pending_customers" method="post" autocomplete="off">
      <input id="min_date" type="hidden" name="min_date" readonly value="<?=$_REQUEST['min_date']?>" />
      <input id="max_date" type="hidden" name="min_date" readonly value="<?=$_REQUEST['max_date']?>" />
      <table>
         <tr>
            <th><span class="label"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> Start Date</th>
            <th><span class="label"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> End Date</th>
            <th><span class="label"><i class="fa fa-filter" aria-hidden="true"></i> Status</span></th>
         </tr>
         <tr>
            <td><input type="text" id="dateStart" name="dateStart" class="value input" value="<?=$_REQUEST['dateStart']?>" /></td>
            <td><input type="text" id="dateEnd" name="dateEnd" class="value input" value="<?=$_REQUEST['dateEnd']?>" /></td>
            <td style="width: 50%;">
              <?php foreach ($queuedCustomers->possibleStatus as $possibleStatus) { ?>
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
    <h3 class="success-message"><i class="fa fa-check-square-o" aria-hidden="true"></i> Customer Updated</h3>
<?php
}
if ($page->error) {
?>
    <h4 class="error-message"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Error has occurred updating customer</h4>
<?php
}
?>
<h2 style="display: inline-block;">Pending Customers
    <?=($page->isSearchResults)? "[Found Customers: ". count($queuedCustomersList)."]" : "";?>
</h2>
<!--	START First Table -->
	<div class="tableBody" style="min-width: 100%;">
	<div class="tableRowHeader">
	    <div class="tableCell">Action</div>
    	<div class="tableCell">Status</div>
    	<div class="tableCell">Date</div>	
		<div class="tableCell">Name</div>
		<div class="tab,cleCell">Address</div>
		<div class="tableCell">Phone</div>
		<div class="tableCell">Info</div>
		<div class="tableCell">Admin Notes</div>
	</div>
<?php
	foreach ($queuedCustomersList as $queuedCustomer) {
?>
	<div class="tableRow">
    	<div class="tableCell" style="font-size: 10px; max-width: 100px;">
			<form method="POST" id="customer_assign_form_<?=$queuedCustomer->id?>" action="/_register/pending_customers?search=<?=$_REQUEST['search']?>">
		    	<?php
		    	switch ($queuedCustomer->status) {
		    	    case 'PENDING':
		    	        ?>
		            	    <button type="button" onclick="assignCustomer(<?=$queuedCustomer->id?>)"><i class="fa fa-check-circle" aria-hidden="true"></i> Assign</button>
		            	    <button type="button" onclick="denyCustomer(<?=$queuedCustomer->id?>)"><i class="fa fa-ban" aria-hidden="true"></i> Deny</button>    	    
		    	        <?php
		    	        break;
		    	
		    	    case 'VERIFYING':
		    	        ?>
		        	        <span style="color: <?=colorCodeStatus("VERIFYING")?>">
		                	    <i class="fa fa-clock-o" aria-hidden="true"></i> email validating
		                	</span>
		    	        <?php
		    	        break;
		    	    case 'APPROVED':
		                ?>
		        	        <span style="color: <?=colorCodeStatus("APPROVED")?>">
		                	    <i class="fa fa-check-circle" aria-hidden="true"></i> approved
		                	</span>
		    	        <?php
		    	        break;
		    	    default:
		    	        ?>
		        	        <span style="color: <?=colorCodeStatus("DENIED")?>">
		                	    <i class="fa fa-times" aria-hidden="true"></i> denied
		                	</span>
		    	        <?php
		    	        break;
		    	}
		    	?>
		        <input id="customer_assign_<?=$queuedCustomer->id?>" type="hidden" name="action" value="assignCustomer"/>
		        <input type="hidden" name="id" value="<?=$queuedCustomer->id?>"/>
			</form>
    	</div>
		<div class="tableCell">
		    <div id="customer_status_form_<?=$queuedCustomer->id?>" class="hidden customer_status_form">
		        <form method="POST" action="/_register/pending_customers?search=<?=$_REQUEST['search']?>">
                    Update Status:<br/>
                    <select name="status">
                        <?php foreach ($queuedCustomers->possibleStatus as $possibleStatus) { ?>
                            <option value="<?=$possibleStatus?>" <?=($queuedCustomer->status == $possibleStatus) ? 'selected="selected"' : ""?>><?=$possibleStatus?></option>
                        <?php } ?>
                    </select>
                    <br/>
		            <input type="hidden" name="action" value="updateStatus"/>
		            <input type="hidden" name="id" value="<?=$queuedCustomer->id?>"/>
		            <button type="submit">Save</button>
		            <button type="button" onclick="cancelEditStatus(<?=$queuedCustomer->id?>)">Cancel</button>
		        </form>
		    </div>
            <div id="customer_status_form_links_<?=$queuedCustomer->id?>" class="customer_status_form_links">		 
            <br/>   
		        <span style="color: <?=colorCodeStatus($queuedCustomer->status)?>">
        		    <?=$queuedCustomer->status?>
		        </span>
                <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        		<a class="small-text cursor-pointer" onclick="editStatus(<?=$queuedCustomer->id?>)"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>
		        <br/><br/>
		    </div>
		</div>
		<div class="tableCell">
		    <?=date("F j, Y, g:i a", strtotime($queuedCustomer->date_created))?>
		</div>
		<div class="tableCell">
		    <?=$queuedCustomer->name?>
		</div>
		<div class="tableCell">
		    <?=$queuedCustomer->address?><br/>
		    <?=$queuedCustomer->city?>,
		    <?=$queuedCustomer->state?>
		    <?=$queuedCustomer->zip?>
		</div>
		<div class="tableCell">
            <?php if ($queuedCustomer->phone) { ?>
                <strong>Main:</strong> <?=$queuedCustomer->phone?><br/>
            <?php } ?>
            <?php if ($queuedCustomer->cell) { ?>
                <strong>Cell:</strong> <?=$queuedCustomer->cell?><br/>
            <?php } ?>
		</div>
		<div class="tableCell">
		    <?php if ($queuedCustomer->product_id) { ?>
		        <strong>Owns Product:</strong> <?=$queuedCustomer->product_id?><br/>
		        <strong>Serial #:</strong> <?=$queuedCustomer->serial_number?><br/>
		    <?php } ?>
		    <?php if ($queuedCustomer->is_reseller) { ?>
                <br/><strong>Is a Reseller</strong><br/>
                Reseller ID: 
                <?=$queuedCustomer->assigned_reseller_id?>
		    <?php } ?>   
		</div>
		<div class="tableCell">
		    <div id="customer_notes_form_<?=$queuedCustomer->id?>" class="hidden customer_notes_form">
		        <form method="POST" action="/_register/pending_customers?search=<?=$_REQUEST['search']?>">
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
