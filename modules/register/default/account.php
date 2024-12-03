<!--Testing this out-->
<script type="text/javascript">
	function submitForm() {

		// make sure that all the notify contacts have a 'description' value populated
		var contactTable = document.getElementById("contact-main-table");
		var notifyChecked = contactTable.getElementsByTagName("input");
		for (var i = 0; i < notifyChecked.length; i++) {
			if (notifyChecked[i].checked) {
				var matches = notifyChecked[i].name.match(/\[[0-9]+\]/);
				if (matches[0]) {
					contactDescriptionField = document.getElementsByName("description[" + matches[0].replace('[', '').replace(']', '') + "]");
					contactDescriptionField[0].style.border = "";
					if (!contactDescriptionField[0].value) {
						alert("Please enter a 'Description' value for all notify (checked) Methods of Contact");
						contactDescriptionField[0].style.border = "3px solid red";
						return false;
					}
				}
			}
		}

		if (document.register.password.value.length > 0 || document.register.password_2.value.length > 0) {
			if (document.register.password.value.length < 6) {
				alert("Your password is too short.");
				return false;
			}

			if (document.register.password.value != document.register.password_2.value) {
				alert("Your passwords don't match.");
				return false;
			}
		}
		return true;
	}

	// submit a delete contact with the hidden form
	function submitDelete(contactId) {
		var confirmDelete = confirm("Delete contact entry for user?");
		if (confirmDelete == true) {
			document.getElementById("register-contacts-id").value = contactId;
			document.getElementById("delete-contact").submit();
		}
	}

	// Redirect user to reset password page
	function passChange() {
		window.location.replace("/_register/reset_password");
		return true;
	}
</script>
<style>
	input:disabled, select:disabled {
		background: #eeeee4;
	}
</style>
<h2>My Account</h2>
<?php if ($page->errorCount() > 0) { ?>
	<section id="form-message">
		<ul class="connectBorder progressText">
			<li style="color:red;"><?= $page->errorString() ?></li>
		</ul>
	</section>
	</div>

<?php }
if ($page->success) {	?>
	<section id="form-message">
		<ul class="connectBorder progressText">
			<li><?= $page->success ?></li>
		</ul>
	</section>
<?php } 
if ($canView) {
?>
	<form name="register" action="<?= PATH ?>/_register/account" method="POST">
		<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
		<input type="hidden" name="target" value="<?= $target ?>" />
		<input type="hidden" name="customer_id" value="<?= $customer_id ?>" />

		<?php if (!$readOnly) { ?>
			<section id="form-message">
				<ul class="connectBorder infoText">
					<li>Make changes and click 'Apply' to complete.</li>
				</ul>
			</section>
		<?php } ?>
		<div class="form_instruction"></div>

		<section class="form-group">
			<ul class="form-grid three-col">
				<h4>Account Information</h4>
				<li id="accountEmailQuestion">
					<label for="status">Status:</label>
					<span id="status" class="value"><?= $queuedCustomer->status ?></span>
					<?php
					if ($queuedCustomer->status == "VERIFYING") {
					?>
						<input type="submit" name="method" value="Resend Email" class="button submitButton registerSubmitButton" />
					<?php
					}
					?>
				</li>
				<li id="accountLoginQuestion">
					<label for="user_name">Login:</label>
					<span class="value"><?= $customer->code ?></span>
				</li>
				<li id="accountFirstNameQuestion">
					<label for="first_name">*First Name:</label>
					<input type="text" name="first_name" value="<?= $customer->first_name ?>" <?php if ($readOnly) echo 'disabled'; ?> />
				</li>
				<li id="accountLastNameQuestion">
					<label for="last_name">*Last Name:</label>
					<input type="text" class="value registerValue registerLastNameValue" name="last_name" value="<?= $customer->last_name ?>" <?php if ($readOnly) echo 'disabled'; ?> />
				<li id="accountOrganizationQuestion">
					<label for="">*Organization:</label>
					<span class="value registerValue"><?= $customer->organization()->name ?></span>
				</li>
				<li id="accountTimeZoneQuestion">
					<label for="">*Time Zone:</label>
					<select id="timezone" name="timezone" class="value input collectionField" <?php if ($readOnly) echo 'disabled'; ?>>
						<?php foreach (timezone_identifiers_list() as $timezone) {
							if (isset($customer->timezone)) $selected_timezone = $customer->timezone;
							else $selected_timezone = 'UTC';
						?>
							<option value="<?= $timezone ?>" <?php if ($timezone == $selected_timezone) print " selected"; ?>><?= $timezone ?></option>
						<?php } ?>
					</select>
				</li>
				<li>
				</li>
			</ul>
		</section>

		<!-- Contact Options -->
		<section class="form-group">
			<h4>Add Methods of Contact</h4>
			<div id="contact-main-table" class="tableBody">
				<div class="tableRowHeader">
					<div class="tableCell">Types</div>
					<div class="tableCell">Description</div>
					<div class="tableCell">Address/Number</div>
					<div class="tableCell">Notes</div>
					<div class="tableCell">Notify</div>
					<div class="tableCell">Drop</div>
				</div>
				<?php foreach ($contacts as $contact) { ?>
					<div class="contact_method_row tableRow">
						<div class="tableCell">
							<span class="hiddenDesktop value">Types: </span>
							<select class="contact_type_value value input" name="type[<?= $contact->id ?>]" <?php if ($readOnly) echo 'disabled'; ?>>
								<?php foreach (array_keys($contact_types) as $contact_type) { ?>
									<option value="<?= $contact_type ?>" <?php if ($contact_type == $contact->type) print " selected"; ?>><?= $contact_types[$contact_type] ?></option>
								<?php	} ?>
							</select>
						</div>
						<div class="tableCell">
							<span class="hiddenDesktop value">Description: </span>
							<input type="text" name="description[<?= $contact->id ?>]" class="value input contactDescriptionColumn" value="<?= $contact->description ?>" <?php if ($readOnly) echo 'disabled'; ?> />
						</div>
						<div class="tableCell">
							<span class="hiddenDesktop value">Address/Number: </span>
							<input type="text" name="value[<?= $contact->id ?>]" class="value input contactValueColumn" value="<?= $contact->value ?>" <?php if ($readOnly) echo 'disabled'; ?> />
						</div>
						<div class="tableCell">
							<span class="hiddenDesktop value">Notes: </span>
							<input type="text" name="notes[<?= $contact->id ?>]" class="value input contactNotesColumn" value="<?= $contact->notes ?>" <?php if ($readOnly) echo 'disabled'; ?> />
						</div>
						<div class="tableCell">
							<span class="hiddenDesktop value">Notify: </span>
							<input type="checkbox" class="contact_notify" name="notify[<?= $contact->id ?>]" value="1" <?php if ($contact->notify) print "checked"; ?> <?php if ($readOnly) echo 'disabled'; ?> />
						</div>
						<div class="tableCell">
							<span class="hiddenDesktop value">Drop: </span>
							<input type="button" name="drop_contact[<?= $contact->id ?>]" class="deleteButton" value="X" onclick="submitDelete(<?= $contact->id ?>)" <?php if ($readOnly) echo 'disabled'; ?> />
						</div>
					</div>
				<?php } ?>
				<div class="tableRow">
					<div class="tableCell">
						<select class="value input" name="type[0]" <?php if ($readOnly) echo 'disabled'; ?>>
							<option value="0">Select</option>
							<?php foreach (array_keys($contact_types) as $contact_type) { ?>
								<option value="<?= $contact_type ?>"><?= $contact_types[$contact_type] ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="tableCell"><input type="text" name="description[0]" class="value input contactDescriptionColumn" <?php if ($readOnly) echo 'disabled'; ?> /></div>
					<div class="tableCell"><input type="text" name="value[0]" class="value input contactValueColumn" <?php if ($readOnly) echo 'disabled'; ?> /></div>
					<div class="tableCell"><input type="text" name="notes[0]" class="value input contactNotesColumn" <?php if ($readOnly) echo 'disabled'; ?> /></div>
					<div class="tableCell"></div>
					<div class="tableCell"></div>
				</div><!-- END tableRow -->
			</div>
		</section>
		<?php if (!$readOnly) { ?>
		<section class="form-group">
			<div id="accountFormSubmit"><input type="submit" name="method" value="Apply" class="button submitButton registerSubmitButton" onclick="return submitForm();" <?php if ($readOnly) echo 'disabled'; ?> /></div>
			<div id="accountFormSubmit"><input type="button" name="method" value="Change Password" class="button submitButton registerSubmitButton" onclick="return passChange();" <?php if ($readOnly) echo 'disabled'; ?> /></div>
		</section>
		<?php } ?>

	</form>

	<!-- hidden for for "delete contact" -->
	<form id="delete-contact" name="delete-contact" action="<?= PATH ?>/_register/account" method="post">
		<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
		<input type="hidden" id="submit-type" name="submit-type" value="delete-contact" />
		<input type="hidden" id="register-contacts-id" name="register-contacts-id" value="" />
	</form>
<?php
}
?>
