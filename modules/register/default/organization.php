<style type="text/css">
	.organizationMemberLoginHeader {
		width: 150px;
	}
	.organizationMemberFirstNameHeader {
		width: 150px;
	}
	.organizationMemberLastNameHeader{
		width: 150px;
	}
	input.registerInput {
		width: 350px;
	}
</style>
<form name="orgDetails" method="POST">
<input type="hidden" name="organization_id" value="<?=$organization->id?>"/>
<span class="title">Organization Details</span>
<?  if ($GLOBALS['_page']->error) { ?>
<div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?  }
	elseif ($GLOBALS['_page']->success) {
?>
<div class="form_success"><?=$GLOBALS['_page']->success?></div>
<?  } ?>
<div class="form_instruction">Make changes and click 'Apply' to complete.</div>

<div id="organizationCodeQuestion" class="registerQuestion">
	<span id="organizationCodeLabel" class="label registerLabel">Code:</span>
	<input name="code" id="organizationCodeValue" class="value input registerInput" value="<?=$organization->code?>" />
</div>
<div id="organizationNameQuestion" class="registerQuestion">
	<span id="organizationNameLabel" class="label registerLabel">Name:</span>
	<input name="name" id="organizationNameValue" class="value input registerInput" value="<?=$organization->name?>" />
</div>
<div id="organizationStatusQuestion" class="registerQuestion">
	<span id="organizationStatusLabel" class="label registerLabel">Status:</span>
	<select name="status" id="organizationStatusValue" class="value input registerInput">
<?		foreach (array("NEW","ACTIVE","EXPIRED","HIDDEN","DELETED") as $status) { ?>
		<option value="<?=$status?>"<? if ($status == $organization->status) print " selected"; ?>><?=$status?></option>
<?		} ?>
	</select>
</div>
<?	if ($organization->id) { ?>
<table class="body" style="margin-top: 10px">
<tr><td class="label organizationMemberLoginHeader">Login</td>
	<td class="label organizationMemberFirstNameHeader">First Name</td>
	<td class="label organizationMemberLastNameHeader">Last Name</td>
</tr>
<?	foreach ($members as $member) { ;?>
<tr><td class="value organizationMemberLogin"><a href="/_register/account?customer_id=<?=$member->id?>"><?=$member->login?></a></td>
	<td class="value organizationMemberFirstName"><?=$member->first_name?></td>
	<td class="value organizationMemberLastName"><?=$member->last_name?></td>
</tr>
<?	} ?>
<tr><td class="value organizationMemberLogin"><input type="text" name="new_login" class="value input" /></td>
	<td class="value organizationMemberFirstName"><input type="text" name="new_first_name" class="value input" /></td>
	<td class="value organizationMemberLastName"><input type="text" name="new_last_name" class="value input" /></td>
</tr>
</table>
<?	} ?>
<div id="accountFormSubmit">
	<input type="submit" name="method" value="Apply" class="button submitButton registerSubmitButton"/>
</div>
</form>