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

<h2>Customer Account Settings</h2>
<form name="register" action="<?=PATH?>/_register/admin_account" method="POST">
<input type="hidden" name="target" value="<?=$target?>"/>
<input type="hidden" name="customer_id" value="<?=$customer_id?>"/>
<?  if ($page->error) { ?>
<div><?=$page->error?></div>
<?  }
	if ($page->success) {
?>
<div class="form_success"><?=$page->success?></div>
<?  } ?>
<div class="form_instruction">Make changes and click 'Apply' to complete.</div>

<!--	 Login Details -->
<div id="accountLoginQuestion" class="login-area">
	<span class="label" style="display: inline;">Login:</span>
	<span class="value"><?=$customer->login?></span>
	<span class="value">[<?=$customer->auth_method?>]</span>
</div>
	
<!--	Start LOGIN Specs -->
<div class="tableBody clean min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 30%;">Organization</div>
		<div class="tableCell" style="width: 20%;">First Name</div>
		<div class="tableCell" style="width: 20%;">Last Name</div>
		<div class="tableCell" style="width: 30%;">Time Zone</div>
	</div> <!-- end row header -->
	<div class="tableRow">
		<div class="tableCell">
			<select class="value input registerValue" name="organization_id">
		<option value="">Select</option>
	<?	foreach ($organizations as $organization) {	?>
		<option value="<?=$organization->id?>"<? if ($organization->id == $customer->organization->id) print " selected"; ?>><?=$organization->name?></option>
	<?	} ?>
	</select>
		</div>
		<div class="tableCell">
			<input type="text" class="value input registerValue registerFirstNameValue" name="first_name" value="<?=$customer->first_name?>" />
		</div>
		<div class="tableCell">
			<input type="text" class="value registerValue registerLastNameValue" name="last_name" value="<?=$customer->last_name?>" />
		</div>
		<div class="tableCell">
			<select id="timezone" name="timezone" class="value input collectionField">
<?	foreach (timezone_identifiers_list() as $timezone) {
		if (isset($customer->timezone)) $selected_timezone = $customer->timezone;
		else $selected_timezone = 'UTC';
?>
		<option value="<?=$timezone?>"<? if ($timezone == $selected_timezone) print " selected"; ?>><?=$timezone?></option>
<?	} ?>
	</select>
		</div>
	</div>
</div>
<!--End LOGIN Specs -->		
	
	
<!-- START Methods of Contact -->
<h3>Methods of Contact</h3>
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 20%;">Type</div>
		<div class="tableCell" style="width: 25%;">Description</div>
		<div class="tableCell" style="width: 30%;">Address/Number</div>
		<div class="tableCell" style="width: 15%;">Notes</div>
		<div class="tableCell" style="width: 5%;">Notify</div>
		<div class="tableCell" style="width: 5%;">Drop</div>
	</div> <!-- end row header -->
	<?	foreach ($contacts as $contact) { ?>
	<div class="tableRow">
		<div class="tableCell">
			<select class="value input" name="type[<?=$contact->id?>]">
				<?		foreach (array_keys($contact_types) as $contact_type) { ?>
				<option value="<?=$contact_type?>"<? if ($contact_type == $contact->type) print " selected";?>><?=$contact_types[$contact_type]?></option>
				<?		} ?>
			</select>
		</div>
		<div class="tableCell">
			<input type="text" name="description[<?=$contact->id?>]" class="value wide_100per" value="<?=$contact->description?>" />
		</div>
		<div class="tableCell">
			<input type="text" name="value[<?=$contact->id?>]" class="value wide_100per" value="<?=$contact->value?>" />
		</div>
		<div class="tableCell">
			<input type="text" name="notes[<?=$contact->id?>]" class="value wide_100per" value="<?=$contact->notes?>" />
		</div>
		<div class="tableCell">
			<input type="checkbox" name="notify[<?=$contact->id?>]" value="1"<? if ($contact->notify) print " checked"; ?> />
		</div>
		<div class="tableCell">
			<input type="button" name="drop_contact[<?=$contact->id?>]" class="deleteButton" value="X" />
		</div>
	</div>
<?	} ?>
	<div class="tableRow">
		<div class="tableCell">
			<select class="value input" name="type[0]">
			<option value="0">Select</option>
			<?	foreach (array_keys($contact_types) as $contact_type) { ?>
			<option value="<?=$contact_type?>"><?=$contact_types[$contact_type]?></option>
			<?	} ?>
		</select>
		</div>
		<div class="tableCell">
			<input type="text" name="description[0]" class="value wide_100per" />
		</div>
		<div class="tableCell">
			<input type="text" name="value[0]" class="value wide_100per" />
		</div>
		<div class="tableCell">
			<input type="text" name="notes[0]" class="value wide_100per" />
		</div>
		<div class="tableCell">
			<input type="checkbox" name="notify[0]" value="1" />
		</div>
		<div class="tableCell">

		</div>
	</div>
</div>
<!--	END Methods of Contact -->		
	

<!--	START Change Password-->
<?  if ($customer->auth_method == 'local') { ?>
<h3 class="marginTop_20">Change Password</h3>
<div class="form_instruction">Leave both fields empty for your password to stay the same.</div>
<div class="tableBody clean min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 25%;">New Password</div>
		<div class="tableCell" style="width: 25%;">Confirm New Password</div>
		<div class="tableCell" style="width: 50%;"></div>
	</div> <!-- end row header -->
	<div class="tableRow">
		<div class="tableCell">
			<input type="password" class="value wide_100per" name="password" />
		</div>
		<div class="tableCell">
			<input type="password" class="value wide_100per" name="password_2" />
		</div>
		<div class="tableCell">
			
		</div>
	</div>
</div>	
<?  } ?>
<div id="accountFormSubmit">
	<input type="submit" name="method" value="Apply" class="button submitButton registerSubmitButton" onclick="return submitForm();" />
</div>
<!--	END Change Password-->
	
<h3>Status</h3>
<select class="input" name="status">
<?	foreach(array('NEW','ACTIVE','EXPIRED','DELETED') as $status) {?>
<!-- <?=$status?> vs <?=$customer->status?> -->
	<option value="<?=$status?>"<? if ($status == $customer->status) print " selected"; ?>><?=$status?></option>
<?	}	?>
</select>
<h3>Assigned Roles</h3>
<table cellpadding="0" cellspacing="0" class="body">
<tr>
	<th class="label" style="width: 10%; ">&nbsp;</th>
	<th class="label" style="width: 25%;">Name</th>
	<th class="label" style="width: 65%;">Description</th>
</tr>
<?	$greenbar = '';
	foreach($all_roles as $role) {
?>
<tr><td class="value<?=$greenbar?>"><input type="checkbox" name="role[<?=$role->id?>]" value="1" <? if ($customer->has_role($role->name)) print " CHECKED";?>/></td><td class="value<?=$greenbar?>"><?=$role->name?></td><td class="value<?=$greenbar?>"><?=$role->description?></td></tr>
<?		if ($greenbar) $greenbar = '';
		else $greenbar = ' greenbar';
	}
?>
</table>
</form>
