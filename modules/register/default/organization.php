<style type="text/css">
/*
	.organizationMemberLoginHeader {
		width: 150px;
	}
	.organizationMemberFirstNameHeader {
		width: 150px;
	}
	.organizationMemberLastNameHeader {
		width: 150px;
	}
	.organizationMemberStatusHeader {
		width: 150px;
	}
	.organizationMemberLastActiveHeader {
		width: 250px;
	}
*/
</style>
<form name="orgDetails" method="POST">
<input type="hidden" name="organization_id" value="<?=$organization->id?>"/>
<div class="title">Organization Details</div>
<?  if ($GLOBALS['_page']->error) { ?>
<div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?  }
	elseif ($GLOBALS['_page']->success) {
?>
<div class="form_success"><?=$GLOBALS['_page']->success?></div>
<?  } ?>
<div class="form_instruction">Make changes and click 'Apply' to complete.</div>

<div id="organizationCodeQuestion" class="input-vert">
	<span id="organizationCodeLabel" class="label">Code:</span>
	<input name="code" type="text" id="organizationCodeValue" value="<?=$organization->code?>" />
</div>
<div id="organizationNameQuestion" class="input-vert">
	<span id="organizationNameLabel" class="label">Name:</span>
	<input name="name" type="text" id="organizationNameValue" value="<?=$organization->name?>" />
</div>
<div id="organizationStatusQuestion" class="input-vert">
	<span id="organizationStatusLabel" class="label">Status:</span>
	<select name="status" id="organizationStatusValue">
<?		foreach (array("NEW","ACTIVE","EXPIRED","HIDDEN","DELETED") as $status) { ?>
		<option value="<?=$status?>"<? if ($status == $organization->status) print " selected"; ?>><?=$status?></option>
<?		} ?>
	</select>
</div>
<div id="organizationResellerBool" class="input-vert">
	<span id="organizationIsResellerLabel" class="label">Can Resell?</span>
	<input name="is_reseller" type="checkbox" value="1" <? if($organization->is_reseller) print " checked"?> />
</div>
<div id="organizationResellerId" class="input-vert">
	<span id="organizationResellerLabel" class="label">Reseller:</span>
	<select name="assigned_reseller_id">
		<option value="">Select</option>
<?	foreach ($resellers as $reseller) {
	if ($organization->id == $reseller->id) continue;
?>
		<option value="<?=$reseller->id?>"<? if($organization->reseller->id == $reseller->id) print " selected";?>><?=$reseller->name?></option>
<?	} ?>
	</select>
</div>
<div id="organizationNotesQuestion" class="input-vert">
	<span id="organizationNotesLabel" class="label">Notes</span>
	<textarea name="notes"><?=$organization->notes?></textarea>
</div>
<?	if ($organization->id) { ?>
<table class="body" style="margin-top: 10px">
<tr><th class="label organizationMemberLoginHeader">Login</th>
	<th class="label organizationMemberFirstNameHeader">First Name</th>
	<th class="label organizationMemberLastNameHeader">Last Name</th>
	<th class="label organizationMemberStatusHeader">Status</th>
	<th class="label organizationMemberLastActiveHeader">Last Active</th>
</tr>
<?	foreach ($members as $member) { ;?>
<tr><td class="value organizationMemberLogin"><a href="/_register/admin_account?customer_id=<?=$member->id?>"><?=$member->login?></a></td>
	<td class="value organizationMemberFirstName" ><?=$member->first_name?></td>
	<td class="value organizationMemberLastName"><?=$member->last_name?></td>
	<td class="value organizationMemberStatus"><?=$member->status?></td>
	<td class="value organizationMemberLastActive" nowrap><?=$member->last_active()?></td>
</tr>
<?	} ?>
<tr>
    <th colspan="5">Add New User</th>
</tr>
<tr class="non-table">
    <td class="value organizationMemberLogin" value="New Username"><input type="text" name="new_login" class="value input" /></td>
	<td class="value organizationMemberFirstName" value="First Name"><input type="text" name="new_first_name" class="value input" /></td>
	<td class="value organizationMemberLastName" value="Last Name"><input type="text" name="new_last_name" class="value input" /></td>
    <td colspan="2">&nbsp;</td>
</tr>
</table>
<?	} ?>
<div id="accountFormSubmit" class="button-bar">
	<input type="submit" name="method" value="Apply" class="button"/>
</div>
</form>