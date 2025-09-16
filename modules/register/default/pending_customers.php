<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>

    // check if the organization already exists for button states
    function checkExisting(id, orgName) {
		var id = parseInt(id.split("_")[1]);

		// Wildcard search
		orgName = orgName+'*';
		var OrgList = Object.create(OrganizationList);
		var organizations = OrgList.find({name: orgName});

		console.log("OrgID: " + id);
		var orgListElem = document.getElementById("organization_list_" + id);
		console.log("OrgList: " + orgListElem);

		while (orgListElem.firstChild) {
			orgListElem.removeChild(orgListElem.firstChild);
		}

		if (organizations.length > 0) {
			var found = false;
			for (var i = 0; i < organizations.length; i++) {
				var org = organizations[i];
				if (typeof(org.name) !== 'undefined') {
					console.log("Adding: " + org.name);
					found = true;
					var option = document.createElement("option");
					option.value = org.name;
					orgListElem.appendChild(option);
				}
				else {
					console.log("No name for org: " + org.id);
				}
			}
			if (found) {
				document.getElementById("organization_"+id + "_assign_button").disabled = false;
				document.getElementById("organization_"+id + "_new_button").disabled = true;
			}
			else {
				document.getElementById("organization_"+id + "_assign_button").disabled = true;
				document.getElementById("organization_"+id + "_new_button").disabled = false;
			}
		}
		else {
			document.getElementById("organization_"+id + "_assign_button").disabled = true;
			document.getElementById("organization_"+id + "_new_button").disabled = false;
		}        
	};

	document.addEventListener("DOMContentLoaded", function() {
		// Reset page to hide all forms initially
		resetPage();
		
		// Check if the organization already exists for button states
		var elements = document.getElementsByClassName("organization");
		for (let i = 0; i < elements.length; i++) {
			var element = elements[i];
			var id = element.id;
			var orgName = element.value;
			checkExisting(id, orgName+"*");
		}
	});

	document.addEventListener("keyup", function(event) {
		if (event.target.classList.contains("organization")) {
			var id = event.target.id;
			var orgName = event.target.value;
			checkExisting(id, orgName);
		}
	});
</script>

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
   
   function assignExistingCustomer(queueId) {
	   $("#customer_add_" + queueId).val('assignCustomer');
	   $("#customer_add_form_" + queueId).submit();
   }
   
   function addNewCustomer(queueId) {
	   $("#customer_add_" + queueId).val('addCustomer');
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
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<div class="form_instruction">
	Manage pending customer registrations. Review and approve or deny customer requests.
	<?=isset($page->isSearchResults) ? "Found " . count($queuedCustomersList) . " customers matching your search criteria." : "";?>
</div>

<!-- ============================================== -->
<!-- PENDING CUSTOMERS LIST -->
<!-- ============================================== -->
<h3>Pending Customers</h3>
<section class="tableBody clean min-tablet">
  <div class="tableRowHeader">
    <div class="tableCell width-15per">Organization</div>
    <div class="tableCell width-15per">Customer Info</div>
    <div class="tableCell width-12per">Status</div>
    <div class="tableCell width-10per">Date</div>
    <div class="tableCell width-15per">Address</div>
    <div class="tableCell width-13per">Contact</div>
    <div class="tableCell width-10per">Product</div>
    <div class="tableCell width-10per">Admin Notes</div>
  </div>
  <?php
    foreach ($queuedCustomersList as $queuedCustomer) {
      $registerCustomer = $queuedCustomer->customer();
      $productItem = new \Product\Item($queuedCustomer->product_id);
      $phone = isset($registerCustomer->contacts(array('type' => 'phone'))[0]) ? $registerCustomer->contacts(array('type' => 'phone'))[0] : "";
      $email = isset($registerCustomer->contacts(array('type' => 'email'))[0]) ? $registerCustomer->contacts(array('type' => 'email'))[0] : "";
	?>
	<div class="tableRow">
		<div class="tableCell">
			<div class="value"><?= htmlspecialchars($queuedCustomer->name) ?></div>
			<?php if ($queuedCustomer->is_reseller) { ?>
			<span class="label" style="color: #007bff; font-size: 0.8em;">[Reseller]</span>
			<?php } ?>
			
			<form method="POST" id="customer_add_form_<?=$queuedCustomer->id?>" action="/_register/pending_customers?search=<?=$_REQUEST['search']?>">
				<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
				<?php
				switch ($queuedCustomer->status) {
				case 'PENDING':
				?>
				<div class="marginTop_10">
					<input list="organization_list_<?=$queuedCustomer->id?>" class="organization value input width-100per" id="organization_<?=$queuedCustomer->id?>" name="organization" value="<?= htmlspecialchars($queuedCustomer->name) ?>" placeholder="Match Organization"/>
					<datalist id="organization_list_<?=$queuedCustomer->id?>"></datalist>
					<div class="button-group marginTop_5">
						<input type="image" class="width-30px" src="/img/icons/icon_cust_add-existing.svg" id="organization_<?=$queuedCustomer->id?>_assign_button" onclick="assignExistingCustomer(<?=$queuedCustomer->id?>)" alt="Assign Existing" title="Assign customer to existing organization" disabled="disabled"/> 
						<input type="image" class="width-30px" src="/img/icons/icon_cust_add-new.svg" id="organization_<?=$queuedCustomer->id?>_new_button" onclick="addNewCustomer(<?=$queuedCustomer->id?>)" alt="Add as New" title="Assign customer to new organization" disabled="disabled"/> 
						<input type="image" class="width-30px" src="/img/icons/icon_cust_deny.svg" id="organization_<?=$queuedCustomer->id?>_deny_button" onclick="denyCustomer(<?=$queuedCustomer->id?>)" alt="Deny" title="Deny customer creation" />
					</div>
				</div>
				<?php
				break;
				case 'VERIFYING':
				?>
				<div class="marginTop_10">
					<span class="register-pending-customers-status-verifying">
						<i class="fa fa-clock-o" aria-hidden="true"></i> Email Validating
					</span>
					<button type="button" class="button secondary marginTop_5" onclick="resend(<?=$queuedCustomer->register_user_id?>)">Resend Verification Email</button>
				</div>
				<?php
				break;
				case 'APPROVED':
				?>
				<div class="marginTop_10">
					<span class="register-pending-customers-status-approved">
						<i class="fa fa-check-circle" aria-hidden="true"></i> Approved
					</span>
				</div>
				<?php
				break;
				default:
				?>
				<div class="marginTop_10">
					<span class="register-pending-customers-status-denied">
						<i class="fa fa-times" aria-hidden="true"></i> Denied
					</span>
				</div>
				<?php
				break;
				}
				?>
				<input id="customer_add_<?=$queuedCustomer->id?>" type="hidden" name="action" value="assignCustomer"/>
				<input type="hidden" name="id" value="<?=$queuedCustomer->id?>"/>
			</form>
		</div>
    
	  <div class="tableCell">
		  <div class="value"><?= htmlspecialchars($registerCustomer->first_name . ' ' . $registerCustomer->last_name) ?></div>
		  <div class="value marginTop_5" style="font-size: 0.8em; color: #666;">Login: <?= htmlspecialchars($registerCustomer->code) ?></div>
	  </div>
	  <div class="tableCell">
		  <div id="customer_status_form_<?=$queuedCustomer->id?>" class="hidden customer_status_form">
		    <form method="POST" action="/_register/pending_customers?search=<?=$_REQUEST['search']?>">
		      <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
			    <select name="status" class="value input" style="width: 100%; font-size: 0.8em; padding: 2px;">
				    <?php foreach ($possibleStatii as $possibleStatus) { ?>
				      <option value="<?=$possibleStatus?>" <?=($queuedCustomer->status == $possibleStatus) ? 'selected="selected"' : ""?>><?=$possibleStatus?></option>
				    <?php } ?>
			    </select>
			    <input type="hidden" name="action" value="updateStatus"/>
			    <input type="hidden" name="id" value="<?=$queuedCustomer->id?>"/>
			    <div style="margin-top: 3px;">
			      <button type="submit" class="button" style="font-size: 0.7em; padding: 2px 6px;">Save</button>
			      <button type="button" class="button secondary" style="font-size: 0.7em; padding: 2px 6px;" onclick="cancelEditStatus(<?=$queuedCustomer->id?>)">Cancel</button>
			    </div>
			  </form>
		  </div>
		  <div id="customer_status_form_links_<?=$queuedCustomer->id?>" class="customer_status_form_links">
			  <span class="register-pending-customers-status-<?=strtolower($queuedCustomer->status)?>"><?=$queuedCustomer->status?></span><br/>
			  <a class="small-text cursor-pointer" onclick="editStatus(<?=$queuedCustomer->id?>)"><i class="fa fa-pencil" aria-hidden="true"></i> Edit Status</a>
		  </div>
	  </div>
	  <div class="tableCell">
		  <div class="value"><?=date("M j, Y", strtotime($queuedCustomer->date_created))?></div>
		  <div class="value" style="font-size: 0.8em; color: #666;"><?=date("g:i a", strtotime($queuedCustomer->date_created))?></div>
	  </div>
	  <div class="tableCell">
		  <div class="value"><?= htmlspecialchars($queuedCustomer->address) ?></div>
		  <div class="value"><?= htmlspecialchars($queuedCustomer->city . ', ' . $queuedCustomer->state . ' ' . $queuedCustomer->zip) ?></div>
	  </div>
	  <div class="tableCell">
		  <?php if (isset($phone->value)) { ?>
		  <div class="value"><strong>Phone:</strong> <?= htmlspecialchars($phone->value) ?></div>
		  <?php } ?>
		  <?php if (isset($email->value)) { ?>
		  <div class="value"><strong>Email:</strong> <?= htmlspecialchars($email->value) ?></div>
		  <?php } ?>
	  </div>
	  <div class="tableCell">
		  <?php if ($queuedCustomer->product_id) { ?>
		  <div class="value"><?= htmlspecialchars($productItem->name) ?></div>
		  <div class="value" style="font-size: 0.8em; color: #666;">[<?= htmlspecialchars($productItem->code) ?>]</div>
		  <div class="value marginTop_5" style="font-size: 0.8em;">Serial: <?= htmlspecialchars($queuedCustomer->serial_number) ?></div>
		  <?php } else { ?>
		  <div class="value" style="color: #999;">No product</div>
		  <?php } ?>
	  </div>
	  <div class="tableCell">
		  <div id="customer_notes_form_<?=$queuedCustomer->id?>" class="hidden customer_notes_form">
        <form method="POST" action="/_register/pending_customers?search=<?=$_REQUEST['search']?>">
          <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
          <div>
            <textarea name="notes" class="value input" style="width: 100%; font-size: 0.8em; padding: 4px; resize: vertical; display: block; height: 40px;" placeholder="Enter admin notes..."><?=htmlspecialchars($queuedCustomer->notes ?? '')?></textarea>
          </div>
          <input type="hidden" name="action" value="updateNotes"/>
          <input type="hidden" name="id" value="<?=$queuedCustomer->id?>"/><br/><br/><br/>
          <div style="margin-top: 3px; white-space: nowrap;">
            <button type="submit" class="button" style="font-size: 0.7em; padding: 2px 6px; margin-right: 5px;">Save</button>
            <button type="button" class="button secondary" style="font-size: 0.7em; padding: 2px 6px;" onclick="cancelEditNote(<?=$queuedCustomer->id?>)">Cancel</button>
          </div>
        </form>
		  </div>
		  <div id="customer_notes_edit_links_<?=$queuedCustomer->id?>" class="customer_notes_edit_links">
		    <?php if ($queuedCustomer->notes) { ?>
		    <div class="value"><?= htmlspecialchars($queuedCustomer->notes) ?></div>
		    <?php } else { ?>
		    <div class="value" style="color: #999; font-style: italic;">No notes</div>
		    <?php } ?>
		    <?php if ($queuedCustomer->notes) { ?>
		    <a class="small-text cursor-pointer" onclick="editNote(<?=$queuedCustomer->id?>)"><i class="fa fa-pencil-square" aria-hidden="true"></i> Edit Note</a>
		    <?php } else { ?>
		    <a class="small-text cursor-pointer" onclick="editNote(<?=$queuedCustomer->id?>)"><i class="fa fa-plus-square" aria-hidden="true"></i> Add Note</a>
		    <?php } ?>
		  </div>
	  </div>
  </div>
  <?php	} ?>
</section>
<!--	END Pending Customers Table -->
