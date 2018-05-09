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
<style type="text/css">
	.accountInfo {
		border-top: 1px solid gray;
	}
	.accountRoles {
		position: relative;
		top: 10px;
		display: block;
		clear: both;
		border-top: 1px solid gray;
	}
	.contactValueColumn {
		width: 200px;
	}
	.contactNotesColumn {
		width: 300px;
	}
	div.registerQuestion {
		float: left;
	}
</style>
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
	<? if (role('register manager')) { ?>
	<span class="value">[<?=$customer->auth_method?>]</span>
	<?	} ?>
</div>
<div id="accountFirstNameQuestion" class="registerQuestion">
	<span class="label registerLabel registerFirstNameLabel">*First Name:</span>
	<input type="text" class="value input registerValue registerFirstNameValue" name="first_name" value="<?=$customer->first_name?>" />
</div>
<div id="accountLastNameQuestion" class="registerQuestion">
	<span class="label registerLabel registerLastNameLabel">*Last Name:</span>
	<input type="text" class="value registerValue registerLastNameValue" name="last_name" value="<?=$customer->last_name?>" />
</div>
<div id="accountOrganizationQuestion" class="registerQuestion">
	<span class="label registerLabel registerCustom1Label">*Organization:</span>
<?	if (role("register manager")) { ?>
	<select class="value input registerValue" name="organization_id">
		<option value="">Select</option>
	<?	foreach ($organizations as $organization) {	?>
		<option value="<?=$organization->id?>"<? if ($organization->id == $customer->organization->id) print " selected"; ?>><?=$organization->name?></option>
	<?	} ?>
	</select>
<?	} else { ?>
	<span class="value registerValue"><?=$customer->organization->name?></span>
<?	} ?>
</div>
<div id="accountTimeZoneQuestion" class="registerQuestion">
	<span class="label registerLabel registerTimeZoneLabel">*Time Zone:</span>
	<select id="timezone" name="timezone" class="value input collectionField">
<?	foreach (timezone_identifiers_list() as $timezone) {
		if (isset($customer->timezone)) $selected_timezone = $customer->timezone;
		else $selected_timezone = 'UTC';
?>
		<option value="<?=$timezone?>"<? if ($timezone == $selected_timezone) print " selected"; ?>><?=$timezone?></option>
<?	} ?>
	</select>
</div>
<hr style="width: 100%; color: white; clear: both; height: 0px;"/>
<!-- Contact Options -->
<div class="form_instruction">Add methods of contact.</div>
<table cellpadding="0" cellspacing="0" class="body" style="width:800px">
<tr><td class="label">Type</td>
	<td class="label contactDescriptionColumn">Description</td>
	<td class="label contactValueColumn">Address/Number</td>
	<td class="label contactNotesColumn">Notes</td>
	<td class="label">Drop</td>
</tr>
<?	foreach ($contacts as $contact) { ?>
<tr><td><select class="value input" name="type[<?=$contact->id?>]">
<?		foreach (array_keys($contact_types) as $contact_type) { ?>
			<option value="<?=$contact_type?>"<? if ($contact_type == $contact->type) print " selected";?>><?=$contact_types[$contact_type]?></option>
<?		} ?>
		</select>
	</td>
	<td><input type="text" name="description[<?=$contact->id?>]" class="value input contactDescriptionColumn" value="<?=$contact->description?>" /></td>
	<td><input type="text" name="value[<?=$contact->id?>]" class="value input contactValueColumn" value="<?=$contact->value?>" /></td>
	<td><input type="text" name="notes[<?=$contact->id?>]" class="value input contactNotesColumn" value="<?=$contact->notes?>" /></td>
	<td><input type="button" name="drop_contact[<?=$contact->id?>]" class="deleteButton" value="X" /></td>
</tr>
<?	} ?>
<tr><td><select class="value input" name="type[0]">
			<option value="0">Select</option>
<?	foreach (array_keys($contact_types) as $contact_type) { ?>
			<option value="<?=$contact_type?>"><?=$contact_types[$contact_type]?></option>
<?	} ?>
		</select>
	</td>
	<td><input type="text" name="description[0]" class="value input contactDescriptionColumn" /></td>
	<td><input type="text" name="value[0]" class="value input contactValueColumn" /></td>
	<td><input type="text" name="notes[0]" class="value input contactNotesColumn" /></td>
</tr>
</table>
<?  if ($customer->auth_method == 'local') { ?>
<div class="form_instruction">Fill in below to change your password.  Leave empty for no change.</div>
<div id="accountPasswordQuestion">
	<span class="label registerLabel registerPasswordLabel">*Password:</span>
	<input type="password" class="value registerValue registerPasswordValue" name="password" />
</div>
<div id="accountPasswordConfirm">
	<span class="label registerLabel registerPasswordLabel">*Confirm Password:</span>
	<input type="password" class="value registerValue registerPasswordValue" name="password_2" />
</div>
<?  } ?>
<div id="accountFormSubmit">
	<input type="submit" name="method" value="Apply" class="button submitButton registerSubmitButton" onclick="return submitForm();" />
</div>

<?	if (role("register manager")) { ?>
<span class="title" style="margin-top: 12px; display: block;">Assigned Roles</span>
<table cellpadding="0" cellspacing="0" class="body" style="width: 800px">
<tr><td class="label" style="width: 40px">&nbsp;</td><td class="label" style="width: 200px;">Name</td><td class="label" style="width: 520px;">Description</td></tr>
<?	$greenbar = '';
	foreach($all_roles as $role) {
?>
<tr><td class="value<?=$greenbar?>"><input type="checkbox" name="role[<?=$role->id?>]" value="1" <? if ($customer->has_role($role->name)) print " CHECKED";?>/></td><td class="value<?=$greenbar?>"><?=$role->name?></td><td class="value<?=$greenbar?>"><?=$role->description?></td></tr>
<?		if ($greenbar) $greenbar = '';
		else $greenbar = ' greenbar';
	}
?>
</table>
<?php } ?>
</form>
