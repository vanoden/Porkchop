<script type="text/javascript">
	function submitForm()
	{
		if (document.register.password.value.length > 0 || document.register.password_2.value.length > 0)
		{
			if (document.register.password.value.length < 6)
			{
				alert("Your password is too short.");
				return false;
			}
			
			if (document.register.password.value != document.register.password_2.value)
			{
				alert("Your passwords don't match.");
				return false;
			}
		}
		
		return true;
	}
</script>
<form name="register" action="<?=PATH?>/_register/account" method="POST">
<input type="hidden" name="target" value="<?=$target?>"/>
<input type="hidden" name="customer_id" value="<?=$customer_id?>"/>
<span class="title">Account Settings</span>
<?  if ($page->error) { ?>
<div><?=$page->error?></div>
<?  }
	if ($page->success) {
?>
<div class="form_success"><?=$page->success?></div>
<?  } ?>
<div class="form_instruction">Make changes and click 'Apply' to complete.</div>

<div id="accountLoginQuestion" class="registerQuestion">
	<span class="label registerLabel registerLoginLabel">Login:</span>
	<span class="value"><?=$customer->login?></span>
</div>
<div id="accountFirstNameQuestion" class="registerQuestion">
	<span class="label registerLabel registerFirstNameLabel">First Name:</span>
	<input type="text" class="value input registerValue registerFirstNameValue" name="first_name" value="<?=$customer->first_name?>" />
</div>
<div id="accountLastNameQuestion" class="registerQuestion">
	<span class="label registerLabel registerLastNameLabel">Last Name:</span>
	<input type="text" class="value registerValue registerLastNameValue" name="last_name" value="<?=$customer->last_name?>" />
</div>
	<br>
<div id="accountOrganizationQuestion" class="registerQuestion">
	<span class="label registerLabel registerCustom1Label">Organization:</span>
	<span class="value registerValue"><?=$customer->organization->name?></span>
</div>
<div id="accountTimeZoneQuestion" class="registerQuestion">
	<span class="label registerLabel registerTimeZoneLabel">Time Zone:</span>
	<select id="timezone" name="timezone" class="value input collectionField">
		<optgroup>
<?	foreach (timezone_identifiers_list() as $timezone) {
		if (isset($customer->timezone)) $selected_timezone = $customer->timezone;
		else $selected_timezone = 'UTC';
?>
		<option value="<?=$timezone?>"<? if ($timezone == $selected_timezone) print " selected"; ?>><?=$timezone?></option>
<?	} ?>
		</optgroup>
	</select>
</div>
<hr style="width: 90%; color: white; clear: both; height: 0px;"/>
<!-- Contact Options -->
<div class="form_instruction">Add methods of contact.</div>
<div class="table">
	<div class="table_header">
		<div class="table_cell contactTypeColumn">Type</div>
		<div class="table_cell contactDescriptionColumn">Description</div>
		<div class="table_cell contactValueColumn">Address/Number</div>
		<div class="table_cell contactNotesColumn">Notes</div>
		<div class="table_cell contactNotifyColumn">Notify</div>
		<div class="table_cell contactDropColumn">Drop</div>
	</div>
	<div class="table_body">
<?	foreach ($contacts as $contact) { ?>
	<div class="table_row">
		<div class="table_cell contactTypeColumn">
			<select class="value input contactType" name="type[<?=$contact->id?>]">
				<optgroup>
<?		foreach (array_keys($contact_types) as $contact_type) { ?>
			<option value="<?=$contact_type?>"<? if ($contact_type == $contact->type) print " selected";?>><?=$contact_types[$contact_type]?></option>
<?		} ?>
				</optgroup>
			</select>
		</div>
		<div class="table_cell contactDescriptionColumn">
			<input type="text" name="description[<?=$contact->id?>]" class="value input contactDescription" value="<?=$contact->description?>" />
		</div>
		<div class="table_cell contactValueColumn">
			<input type="text" name="value[<?=$contact->id?>]" class="value input contactValue" value="<?=$contact->value?>" />
		</div>
		<div class="table_cell contactNotesColumn">
			<input type="text" name="notes[<?=$contact->id?>]" class="value input contactNotes" value="<?=$contact->notes?>" />
		</div>
		<div class="table_cell contactNotifyColumn">
			<input type="checkbox" name="notify[<?=$contact->id?>]" value="1"<? if ($contact->notify == true) print " checked"; ?>>
		</div>
		<div class="table_cell contactDropColumn">
			<input type="button" name="drop_contact[<?=$contact->id?>]" class="deleteButton" value="X" />
		</div>
	</div>
<?	} ?>
	<div class="table_row" id="formNewContact">
		<div class="table_cell contactTypeColumn">
			<select class="value input contactType" name="type[0]">
				<option value="0">Select</option>
<?	foreach (array_keys($contact_types) as $contact_type) { ?>
				<option value="<?=$contact_type?>"><?=$contact_types[$contact_type]?></option>
<?	} ?>
			</select>
		</div>
		<div class="table_cell contactDescriptionColumn">
			<input type="text" name="description[0]" class="value input contactDescription" />
		</div>
		<div class="table_cell contactValueColumn">
			<input type="text" name="value[0]" class="value input contactValue" />
		</div>
		<div class="table_cell contactNotesColumn">
			<input type="text" name="notes[0]" class="value input contactNotes" />
		</div>
		<div class="table_cell contactNotifyColumn">
			<input type="checkbox" name="notify[0]" value="1">
		</div>
	</div>
	</div>
</div>
<?  if ($customer->auth_method == 'local') { ?>
<div class="form_instruction">Fill in below to change your password.</div>
<div id="accountPasswordQuestion">
	<span class="label registerLabel registerPasswordLabel">Password:</span>
	<input type="password" class="value registerValue registerPasswordValue" name="password" />
</div>
<div id="accountPasswordConfirm">
	<span class="label registerLabel registerPasswordLabel">Password Again:</span>
	<input type="password" class="value registerValue registerPasswordValue" name="password_2" />
</div>
<?  } ?>
<div id="accountFormSubmit">
	<input type="submit" name="method" value="Apply" class="button submitButton registerSubmitButton" onclick="return submitForm();" />
</div>
</form>
