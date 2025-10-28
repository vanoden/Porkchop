
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
	   var statusForms = document.getElementsByClassName("customer_status_form");
	   for (var i = 0; i < statusForms.length; i++) {
		   statusForms[i].style.display = "none";
	   }
	   
	   var statusLinks = document.getElementsByClassName("customer_status_form_links");
	   for (var i = 0; i < statusLinks.length; i++) {
		   statusLinks[i].style.display = "block";
	   }
	   
	   var notesForms = document.getElementsByClassName("customer_notes_form");
	   for (var i = 0; i < notesForms.length; i++) {
		   notesForms[i].style.display = "none";
	   }
	   
	   var notesLinks = document.getElementsByClassName("customer_notes_edit_links");
	   for (var i = 0; i < notesLinks.length; i++) {
		   notesLinks[i].style.display = "block";
	   }
   }
   
   // edit status for pending customer
   function editStatus(queueId) {
	   resetPage();
	   document.getElementById("customer_status_form_" + queueId).style.display = "block";
	   document.getElementById("customer_status_form_links_" + queueId).style.display = "none";       
   }
   
   // cancel edit status for pending customer
   function cancelEditStatus(queueId) {
	   resetPage();
	   document.getElementById("customer_status_form_" + queueId).style.display = "none";
	   document.getElementById("customer_status_form_links_" + queueId).style.display = "block";       
   }
   
   // edit notes for pending customer
   function editNote(queueId) {
	   resetPage();
	   document.getElementById("customer_notes_form_" + queueId).style.display = "block";
	   document.getElementById("customer_notes_edit_links_" + queueId).style.display = "none";       
   }
   
   // cancel edit notes for pending customer
   function cancelEditNote(queueId) {
	   resetPage();
	   document.getElementById("customer_notes_form_" + queueId).style.display = "none";
	   document.getElementById("customer_notes_edit_links_" + queueId).style.display = "block";       
   }
   
   function assignExistingCustomer(queueId) {
	   document.getElementById("customer_add_" + queueId).value = 'assignCustomer';
	   document.getElementById("customer_add_form_" + queueId).submit();
   }
   
   function addNewCustomer(queueId) {
	   document.getElementById("customer_add_" + queueId).value = 'addCustomer';
	   document.getElementById("customer_add_form_" + queueId).submit();
   }
   
   function denyCustomer(queueId) {
	   document.getElementById("customer_add_" + queueId).value = 'denyCustomer';
	   document.getElementById("customer_add_form_" + queueId).submit();
   }
   
   function resend(customerId) {
	  window.location.href = "/_register/pending_customers?verifyAgain="+customerId;
   }
   
   // date picker with max date being current day
   window.onload = function() {
	  var dateStart = document.getElementById('dateStart');
	  var dateEnd = document.getElementById('dateEnd');
	  
	  if (dateStart) {
		  dateStart.type = 'date';
		  dateStart.max = new Date().toISOString().split('T')[0];
		  dateStart.addEventListener('change', function() {
			  var minDate = document.getElementById('min_date');
			  if (minDate) minDate.value = this.value;
		  });
	  }
	  
	  if (dateEnd) {
		  dateEnd.type = 'date';
		  dateEnd.max = new Date().toISOString().split('T')[0];
		  dateEnd.addEventListener('change', function() {
			  var maxDate = document.getElementById('max_date');
			  if (maxDate) maxDate.value = this.value;
		  });
	  }
   }
</script>

<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<div id="pending-customers-container">

<div class="form_instruction">
	Manage pending customer registrations. Review and approve or deny customer requests.
	<?=isset($page->isSearchResults) ? "Found " . count($queuedCustomersList) . " customers matching your search criteria." : "";?>
</div>

<!-- ============================================== -->
<!-- FILTER FORM -->
<!-- ============================================== -->
<div id="search_container">
	<form method="GET" action="/_register/pending_customers">
		<input type="text" name="search" id="search" placeholder="search" value="<?=htmlspecialchars($_REQUEST['search'] ?? '')?>">
		<input type="text" id="dateStart" name="dateStart" placeholder="From Date" value="<?=htmlspecialchars($_REQUEST['dateStart'] ?? '')?>">
		<input type="text" id="dateEnd" name="dateEnd" placeholder="To Date" value="<?=htmlspecialchars($_REQUEST['dateEnd'] ?? '')?>">
		<input type="checkbox" name="VERIFYING" value="VERIFYING" <?=isset($_REQUEST['VERIFYING']) ? 'checked' : ''?>><label>Verifying</label>
		<input type="checkbox" name="PENDING" value="PENDING" <?=isset($_REQUEST['PENDING']) || (empty($_REQUEST['VERIFYING']) && empty($_REQUEST['PENDING']) && empty($_REQUEST['APPROVED']) && empty($_REQUEST['DENIED'])) ? 'checked' : ''?>><label>Pending</label>
		<input type="checkbox" name="APPROVED" value="APPROVED" <?=isset($_REQUEST['APPROVED']) ? 'checked' : ''?>><label>Approved</label>
		<input type="checkbox" name="DENIED" value="DENIED" <?=isset($_REQUEST['DENIED']) ? 'checked' : ''?>><label>Denied</label>
		<div class="button-group">
			<input type="submit" name="btn_search" value="Search">
			<input type="button" value="Clear" onclick="window.location.href='/_register/pending_customers'">
		</div>
	</form>
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
			<span class="label reseller-label">[Reseller]</span>
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
						<div class="button-item">
							<input type="image" class="width-30px" src="/img/icons/icon_cust_add-existing.svg" id="organization_<?=$queuedCustomer->id?>_assign_button" onclick="assignExistingCustomer(<?=$queuedCustomer->id?>)" alt="Assign Existing" title="Assign customer to existing organization" disabled="disabled"/> 
							<div class="button-label">Assign</div>
						</div>
						<div class="button-item">
							<input type="image" class="width-30px" src="/img/icons/icon_cust_add-new.svg" id="organization_<?=$queuedCustomer->id?>_new_button" onclick="addNewCustomer(<?=$queuedCustomer->id?>)" alt="Add as New" title="Assign customer to new organization" disabled="disabled"/> 
							<div class="button-label">New</div>
						</div>
						<div class="button-item">
							<input type="image" class="width-30px" src="/img/icons/icon_cust_deny.svg" id="organization_<?=$queuedCustomer->id?>_deny_button" onclick="denyCustomer(<?=$queuedCustomer->id?>)" alt="Deny" title="Deny customer creation" />
							<div class="button-label">Deny</div>
						</div>
					</div>
				</div>
				<?php
				break;
				case 'VERIFYING':
				?>
				<div class="marginTop_10">
					<span class="register-pending-customers-status-verifying">
						<img src="/img/contact/icon_form-mail.svg" alt="Email Validating" class="status-icon"> Email Validating
					</span>
					<button type="button" class="button secondary marginTop_5" onclick="resend(<?=$queuedCustomer->register_user_id?>)">Resend Verification Email</button>
				</div>
				<?php
				break;
				case 'APPROVED':
				?>
				<div class="marginTop_10">
					<span class="register-pending-customers-status-approved">
						<img src="/img/_global/circ-letter.svg" alt="Approved" class="status-icon"> Approved
					</span>
				</div>
				<?php
				break;
				default:
				?>
				<div class="marginTop_10">
					<span class="register-pending-customers-status-denied">
						<img src="/img/_global/icon_error.svg" alt="Denied" class="status-icon"> Denied
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
		  <div class="value marginTop_5 login-info">Login: <?= htmlspecialchars($registerCustomer->code) ?></div>
	  </div>
	  <div class="tableCell">
		  <div id="customer_status_form_<?=$queuedCustomer->id?>" class="hidden customer_status_form">
		    <form method="POST" action="/_register/pending_customers?search=<?=$_REQUEST['search']?>">
		      <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
			    <select name="status" class="value input status-select">
				    <?php foreach ($possibleStatii as $possibleStatus) { ?>
				      <option value="<?=$possibleStatus?>" <?=($queuedCustomer->status == $possibleStatus) ? 'selected="selected"' : ""?>><?=$possibleStatus?></option>
				    <?php } ?>
			    </select>
			    <input type="hidden" name="action" value="updateStatus"/>
			    <input type="hidden" name="id" value="<?=$queuedCustomer->id?>"/>
			    <div class="button-group-spacing">
			      <button type="submit" class="button small-button">Save</button>
			      <button type="button" class="button secondary small-button" onclick="cancelEditStatus(<?=$queuedCustomer->id?>)">Cancel</button>
			    </div>
			  </form>
		  </div>
		  <div id="customer_status_form_links_<?=$queuedCustomer->id?>" class="customer_status_form_links">
			  <span class="register-pending-customers-status-<?=strtolower($queuedCustomer->status)?>"><?=$queuedCustomer->status?></span><br/>
			  <a class="small-text cursor-pointer" onclick="editStatus(<?=$queuedCustomer->id?>)"><img src="/img/icons/edit_dk.svg" alt="Edit Status" class="edit-icon"> Edit Status</a>
		  </div>
	  </div>
	  <div class="tableCell">
		  <div class="value"><?=date("M j, Y", strtotime($queuedCustomer->date_created))?></div>
		  <div class="value time-info"><?=date("g:i a", strtotime($queuedCustomer->date_created))?></div>
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
		  <div class="value product-code">[<?= htmlspecialchars($productItem->code) ?>]</div>
		  <div class="value marginTop_5 serial-info">Serial: <?= htmlspecialchars($queuedCustomer->serial_number) ?></div>
		  <?php } else { ?>
		  <div class="value no-product">No product</div>
		  <?php } ?>
	  </div>
	  <div class="tableCell">
		  <div id="customer_notes_form_<?=$queuedCustomer->id?>" class="hidden customer_notes_form">
        <form method="POST" action="/_register/pending_customers?search=<?=$_REQUEST['search']?>">
          <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
          <div>
            <textarea name="notes" class="value input notes-textarea" placeholder="Enter admin notes..."><?=htmlspecialchars($queuedCustomer->notes ?? '')?></textarea>
          </div>
          <input type="hidden" name="action" value="updateNotes"/>
          <input type="hidden" name="id" value="<?=$queuedCustomer->id?>"/><br/><br/><br/>
          <div class="button-spacing">
            <button type="submit" class="button save-button">Save</button>
            <button type="button" class="button secondary small-button" onclick="cancelEditNote(<?=$queuedCustomer->id?>)">Cancel</button>
          </div>
        </form>
		  </div>
		  <div id="customer_notes_edit_links_<?=$queuedCustomer->id?>" class="customer_notes_edit_links">
		    <?php if ($queuedCustomer->notes) { ?>
		    <div class="value"><?= htmlspecialchars($queuedCustomer->notes) ?></div>
		    <?php } else { ?>
		    <div class="value no-notes">No notes</div>
		    <?php } ?>
		    <?php if ($queuedCustomer->notes) { ?>
		    <a class="small-text cursor-pointer" onclick="editNote(<?=$queuedCustomer->id?>)"><img src="/img/icons/edit_on.svg" alt="Edit Note" class="edit-icon"> Edit Note</a>
		    <?php } else { ?>
		    <a class="small-text cursor-pointer" onclick="editNote(<?=$queuedCustomer->id?>)"><img src="/img/icons/icon_tools_add.svg" alt="Add Note" class="edit-icon"> Add Note</a>
		    <?php } ?>
		  </div>
	  </div>
  </div>
  <?php	} ?>
</section>
<!--	END Pending Customers Table -->

</div> End pending-customers-container
