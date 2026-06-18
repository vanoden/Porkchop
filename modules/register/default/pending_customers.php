
<script>
    // CSRF token for form submissions
    var csrfToken = '<?=$GLOBALS['_SESSION_']->getCSRFToken()?>';

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
   
   function resend(customerId, buttonElement) {
	  // Disable button and show loading state
	  if (buttonElement) {
		  buttonElement.disabled = true;
		  var originalText = buttonElement.textContent || buttonElement.innerText;
		  buttonElement.textContent = 'Sending...';
		  buttonElement.setAttribute('data-original-text', originalText);
		  // Style button as grey with white text
		  buttonElement.style.backgroundColor = '#6c757d';
		  buttonElement.style.color = '#ffffff';
		  buttonElement.style.cursor = 'not-allowed';
		  buttonElement.style.opacity = '0.7';
	  }
	  
	  // Preserve all search parameters
	  var params = new URLSearchParams(window.location.search);
	  params.set('verifyAgain', customerId);
	  params.set('csrfToken', csrfToken);
	  window.location.href = "/_register/pending_customers?" + params.toString();
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

<div id="pending-customers-container" class="monitor-admin-list register-pending-customers">

<div class="form_instruction">
	Manage pending customer registrations. Review and approve or deny customer requests.
	<?=isset($page->isSearchResults) ? "Found " . count($queuedCustomersList) . " customers matching your search criteria." : "";?>
</div>

<form class="filter-bar" method="GET" action="/_register/pending_customers">
	<div class="filter-bar__controls">
		<div class="form-field filter-bar__search">
			<label for="search">Search</label>
			<input type="text" name="search" id="search" placeholder="search" value="<?= htmlspecialchars($_REQUEST['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
		</div>
		<div class="form-field">
			<label for="dateStart">From</label>
			<input type="text" id="dateStart" name="dateStart" placeholder="From Date" value="<?= htmlspecialchars($_REQUEST['dateStart'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
		</div>
		<div class="form-field">
			<label for="dateEnd">To</label>
			<input type="text" id="dateEnd" name="dateEnd" placeholder="To Date" value="<?= htmlspecialchars($_REQUEST['dateEnd'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
		</div>
		<div class="form-field form-field--checks pending-customers-filter-checks">
			<span class="form-field__group-label">Status</span>
			<div class="form-field__check-options">
			<label class="check-field">
				<input type="checkbox" name="VERIFYING" value="VERIFYING" <?= isset($_REQUEST['VERIFYING']) ? 'checked' : '' ?>>
				Verifying
			</label>
			<label class="check-field">
				<input type="checkbox" name="PENDING" value="PENDING" <?= isset($_REQUEST['PENDING']) || (empty($_REQUEST['VERIFYING']) && empty($_REQUEST['PENDING']) && empty($_REQUEST['APPROVED']) && empty($_REQUEST['DENIED'])) ? 'checked' : '' ?>>
				Pending
			</label>
			<label class="check-field">
				<input type="checkbox" name="APPROVED" value="APPROVED" <?= isset($_REQUEST['APPROVED']) ? 'checked' : '' ?>>
				Approved
			</label>
			<label class="check-field">
				<input type="checkbox" name="DENIED" value="DENIED" <?= isset($_REQUEST['DENIED']) ? 'checked' : '' ?>>
				Denied
			</label>
			</div>
		</div>
	</div>
	<div class="button-group filter-bar__actions">
		<button type="submit" name="btn_search" value="Search">Search</button>
		<button type="button" class="btn-secondary" onclick="window.location.href='/_register/pending_customers'">Clear</button>
	</div>
</form>

<h2>Pending Customers [<?=count($queuedCustomersList)?>]</h2>
<section class="table-group register-pending-customers-table-wrap">
  <table class="responsive-table responsive-table--banded pending-customers-table">
    <thead>
      <tr>
        <th scope="col" class="pending-customers-col-org">Organization</th>
        <th scope="col" class="pending-customers-col-customer">Customer Info</th>
        <th scope="col" class="pending-customers-col-status">Status</th>
        <th scope="col" class="pending-customers-col-date">Date</th>
        <th scope="col" class="pending-customers-col-address">Address</th>
        <th scope="col" class="pending-customers-col-contact">Contact</th>
        <th scope="col" class="pending-customers-col-product">Product</th>
        <th scope="col" class="pending-customers-col-notes">Admin Notes</th>
      </tr>
    </thead>
    <tbody>
      <?php
        foreach ($queuedCustomersList as $queuedCustomer) {
          $registerCustomer = $queuedCustomer->customer();
          $productItem = new \Product\Item($queuedCustomer->product_id);
          $phone = isset($registerCustomer->contacts(array('type' => 'phone'))[0]) ? $registerCustomer->contacts(array('type' => 'phone'))[0] : "";
          $email = isset($registerCustomer->contacts(array('type' => 'email'))[0]) ? $registerCustomer->contacts(array('type' => 'email'))[0] : "";
      ?>
      <tr>
        <td data-label="Organization" class="pending-customers-col-org">
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
					<div class="button-group pending-customers-org-actions marginTop_5">
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
        </td>
    
	  <td data-label="Customer Info" class="pending-customers-col-customer">
		  <div class="pending-customers-cell-stack">
			  <div class="value"><?= htmlspecialchars($registerCustomer->first_name . ' ' . $registerCustomer->last_name) ?></div>
			  <div class="value pending-customers-meta">Login: <?= htmlspecialchars($registerCustomer->code) ?></div>
		  </div>
	  </td>
	  <td data-label="Status" class="pending-customers-col-status">
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
			  <a class="pending-customers-edit-link" href="#" onclick="editStatus(<?=$queuedCustomer->id?>); return false;"><img src="/img/icons/edit_dk.svg" alt="Edit Status" class="edit-icon"> Edit Status</a>
			  <?php if ($queuedCustomer->status == 'VERIFYING') { ?>
			  <div class="marginTop_5">
				  <button type="button" class="button secondary" onclick="resend(<?=$queuedCustomer->register_user_id?>, this)">Resend Email</button>
			  </div>
			  <?php } ?>
		  </div>
	  </td>
	  <td data-label="Date" class="pending-customers-col-date">
		  <div class="pending-customers-cell-stack pending-customers-date">
			  <div class="value"><?=date('M j, Y', strtotime($queuedCustomer->date_created))?></div>
			  <div class="value pending-customers-meta"><?=date('g:i a', strtotime($queuedCustomer->date_created))?></div>
		  </div>
	  </td>
	  <td data-label="Address" class="pending-customers-col-address">
		  <div class="pending-customers-cell-stack">
			  <div class="value"><?= htmlspecialchars($queuedCustomer->address) ?></div>
			  <div class="value"><?= htmlspecialchars($queuedCustomer->city . ', ' . $queuedCustomer->state . ' ' . $queuedCustomer->zip) ?></div>
		  </div>
	  </td>
	  <td data-label="Contact" class="pending-customers-col-contact">
		  <div class="pending-customers-cell-stack">
<?php if (isset($phone->value)) { ?>
			  <div class="value"><strong>Phone:</strong> <?= htmlspecialchars($phone->value) ?></div>
<?php } ?>
<?php if (isset($email->value)) { ?>
			  <div class="value pending-customers-email"><strong>Email:</strong> <?= htmlspecialchars($email->value) ?></div>
<?php } ?>
		  </div>
	  </td>
	  <td data-label="Product" class="pending-customers-col-product">
		  <div class="pending-customers-cell-stack">
<?php if ($queuedCustomer->product_id) { ?>
			  <div class="value"><?= htmlspecialchars($productItem->name) ?></div>
			  <div class="value pending-customers-meta">[<?= htmlspecialchars($productItem->code) ?>]</div>
			  <div class="value pending-customers-meta">Serial: <?= htmlspecialchars($queuedCustomer->serial_number) ?></div>
<?php } else { ?>
			  <div class="value no-product">No product</div>
<?php } ?>
		  </div>
	  </td>
	  <td data-label="Admin Notes" class="pending-customers-col-notes">
		  <div id="customer_notes_form_<?=$queuedCustomer->id?>" class="hidden customer_notes_form">
        <form method="POST" action="/_register/pending_customers">
          <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
          <div>
            <textarea name="notes" class="value input notes-textarea" placeholder="Enter admin notes..."><?=htmlspecialchars($queuedCustomer->notes ?? '')?></textarea>
          </div>
          <input type="hidden" name="action" value="updateNotes"/>
          <input type="hidden" name="id" value="<?=$queuedCustomer->id?>"/>
          <!-- Preserve search parameters -->
          <?php if (!empty($_REQUEST['search'])) { ?>
          <input type="hidden" name="search" value="<?=htmlspecialchars($_REQUEST['search'])?>">
          <?php } ?>
          <?php if (!empty($_REQUEST['dateStart'])) { ?>
          <input type="hidden" name="dateStart" value="<?=htmlspecialchars($_REQUEST['dateStart'])?>">
          <?php } ?>
          <?php if (!empty($_REQUEST['dateEnd'])) { ?>
          <input type="hidden" name="dateEnd" value="<?=htmlspecialchars($_REQUEST['dateEnd'])?>">
          <?php } ?>
          <?php if (isset($_REQUEST['VERIFYING'])) { ?>
          <input type="hidden" name="VERIFYING" value="VERIFYING">
          <?php } ?>
          <?php if (isset($_REQUEST['PENDING'])) { ?>
          <input type="hidden" name="PENDING" value="PENDING">
          <?php } ?>
          <?php if (isset($_REQUEST['APPROVED'])) { ?>
          <input type="hidden" name="APPROVED" value="APPROVED">
          <?php } ?>
          <?php if (isset($_REQUEST['DENIED'])) { ?>
          <input type="hidden" name="DENIED" value="DENIED">
          <?php } ?>
          <div class="button-spacing">
            <button type="submit" class="button save-button">Save</button>
            <button type="button" class="button btn-secondary small-button" onclick="cancelEditNote(<?=$queuedCustomer->id?>)">Cancel</button>
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
		    <a class="pending-customers-edit-link" href="#" onclick="editNote(<?=$queuedCustomer->id?>); return false;"><img src="/img/icons/edit_on.svg" alt="Edit Note" class="edit-icon"> Edit Note</a>
		    <?php } else { ?>
		    <a class="pending-customers-edit-link" href="#" onclick="editNote(<?=$queuedCustomer->id?>); return false;"><img src="/img/icons/icon_tools_add.svg" alt="Add Note" class="edit-icon"> Add Note</a>
		    <?php } ?>
		  </div>
	  </td>
      </tr>
      <?php	} ?>
    </tbody>
  </table>
</section>

</div>
