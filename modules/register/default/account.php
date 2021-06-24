<!--Testing this out-->
<script type="text/javascript">
	function submitForm() {
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
</script>
<style type="text/css"></style>
<form name="register" action="<?=PATH?>/_register/account" method="POST">
<input type="hidden" name="target" value="<?=$target?>"/>
<input type="hidden" name="customer_id" value="<?=$customer_id?>"/>
<h2>Account Settings</h2>
<?php if ($page->error) { ?>
    <div><?=$page->error?></div>
<?php }
	if ($page->success) {
?>
    <div class="form_success"><?=$page->success?></div>
<?php } ?>
<div class="form_instruction">Make changes and click 'Apply' to complete.</div>

<div id="accountLoginQuestion" class="registerQuestion">
	<span class="label registerLabel registerLoginLabel">Login:</span>
	<span class="value"><?=$customer->login?></span>
</div>
<div id="accountFirstNameQuestion" class="registerQuestion">
	<span class="label registerLabel registerFirstNameLabel">*First Name:</span>
	<input type="text" class="value input registerValue registerFirstNameValue" name="first_name" value="<?=$customer->first_name?>" />
</div>
<div id="accountLastNameQuestion" class="registerQuestion">
	<span class="label registerLabel registerLastNameLabel">*Last Name:</span>
	<input type="text" class="value registerValue registerLastNameValue" name="last_name" value="<?=$customer->last_name?>" />
</div>
	<br>
<div id="accountOrganizationQuestion" class="registerQuestion">
	<span class="label registerLabel registerCustom1Label">*Organization:</span>
	<span class="value registerValue"><?=$customer->organization->name?></span>
</div>
<div id="accountTimeZoneQuestion" class="registerQuestion">
	<span class="label registerLabel registerTimeZoneLabel">*Time Zone:</span>
	<select id="timezone" name="timezone" class="value input collectionField">
<?php foreach (timezone_identifiers_list() as $timezone) {
		if (isset($customer->timezone)) $selected_timezone = $customer->timezone;
		else $selected_timezone = 'UTC';
?>
		<option value="<?=$timezone?>"<?php if ($timezone == $selected_timezone) print " selected"; ?>><?=$timezone?></option>
<?php } ?>
	</select>
</div>

<!-- Contact Options -->
<div class="form_instruction">Add methods of contact</div>
	
<table cellpadding="0" cellspacing="0" class="body contactMethods">
	<tr>
		<td class="label">Types</td>
		<td class="label contactDescriptionColumn">Description</td>
		<td class="label contactValueColumn">Address/Number</td>
		<td class="label contactNotesColumn">Notes</td>
		<td class="label" style="width: 70px;text-align:center;">Notify</td>
		<td class="label" style="width: 70px;text-align:center;">Drop</td>
	</tr>
    
    <?php foreach ($contacts as $contact) { ?>
	    <tr>
        <td>
			    <select class="value input" name="type[<?=$contact->id?>]">
				    <?php	foreach (array_keys($contact_types) as $contact_type) { ?>
					    <option value="<?=$contact_type?>"<?php if ($contact_type == $contact->type) print " selected";?>><?=$contact_types[$contact_type]?></option>
				    <?php	} ?>
			    </select>
		    </td>
		    <td>
			    <input type="text" name="description[<?=$contact->id?>]" class="value input contactDescriptionColumn" value="<?=$contact->description?>" />
		    </td>
		    <td><input type="text" name="value[<?=$contact->id?>]" class="value input contactValueColumn" value="<?=$contact->value?>" /></td>
		    <td><input type="text" name="notes[<?=$contact->id?>]" class="value input contactNotesColumn" value="<?=$contact->notes?>" /></td>
		    <td><input type="checkbox" name="notify[<?=$contact->id?>]" value="1" <?php if ($contact->notify) print "checked"; ?> /></td>
		    <td><input type="button" name="drop_contact[<?=$contact->id?>]" class="deleteButton" value="X"  onclick="submitDelete(<?=$contact->id?>)" /></td>
	    </tr>
    <?php } ?>
    
	<tr>
    <td>
		<select class="value input" name="type[0]">
		    <option value="0">Select</option>
            <?php foreach (array_keys($contact_types) as $contact_type) { ?>
                        <option value="<?=$contact_type?>"><?=$contact_types[$contact_type]?></option>
            <?php } ?>
		</select>
	</td>
	<td><input type="text" name="description[0]" class="value input contactDescriptionColumn" /></td>
	<td><input type="text" name="value[0]" class="value input contactValueColumn" /></td>
	<td><input type="text" name="notes[0]" class="value input contactNotesColumn" /></td>
</tr>
</table>

<?php if ($customer->auth_method == 'local') { ?>
    <div class="form_instruction">Fill in below to change your password.  Leave empty for no change.</div>
    <div id="accountPasswordQuestion">
	    <span class="label registerLabel registerPasswordLabel">*Password:</span>
	    <input type="password" class="value registerValue registerPasswordValue" name="password" />
    </div>
    <div id="accountPasswordConfirm" style="clear:both;">
	    <span class="label registerLabel registerPasswordLabel">*Confirm Password:</span>
	    <input type="password" class="value registerValue registerPasswordValue" name="password_2" />
    </div>
<?php } ?>
<div id="accountFormSubmit">
	<input type="submit" name="method" value="Apply" class="button submitButton registerSubmitButton" onclick="return submitForm();" />
</div>
</form>
<!-- hidden for for "delete contact" -->
<form id="delete-contact" name="delete-contact" action="<?=PATH?>/_register/account" method="post">
   <input type="hidden" id="submit-type" name="submit-type" value="delete-contact"/>
   <input type="hidden" id="register-contacts-id" name="register-contacts-id" value=""/>
</form>
