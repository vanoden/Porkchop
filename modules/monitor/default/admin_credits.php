<?	if (! in_array('monitor admin',$GLOBALS['_SESSION_']->customer->roles))
	{
		print "<span class=\"form_error\">You are not authorized for this view!</span>";
		return;
	}
?>
<script language="Javascript">
	function getCompany()
	{
		document.companyForm.submit();
		return true;
	}
	function addCredits()
	{
		document.creditForm.submit();
		return true;
	}
</script><table class="body" cellpadding="0" cellspacing="0">
<tr><td class="title" colspan="3">Calibration Credit Management</td></tr>
<?	if ($GLOBALS['_page']->success) { ?>
<tr><td class="form_success" colspan="3"><?=$GLOBALS['_page']->success?></td></tr>
<?	} ?>
<?	if ($GLOBALS['_page']->error) { ?>
<tr><td class="form_error" colspan="3"><?=$GLOBALS['_page']->error?></td></tr>
<?	} ?>
<form name="companyForm" method="post" action="/_monitor/admin_credits">
<tr><td class="label">Choose Organization</td>
	<td class="value">
		<select name="organization_id" class="input value">
			<option value="">Select</option>
		<?	foreach ($organizations as $sel_organization) { ?>
			<option value="<?=$sel_organization->id?>"<? if ($sel_organization->id == $_REQUEST['organization_id']) print " selected";?>><?=$sel_organization->name?></option>
		<?	} ?>
		</select>
	</td>
	<td class="value"><input type="button" name="btn_credits" class="button" value="Get Credits" onclick="getCompany()" /></td>
</tr>
</form>
</table>
<br>
<?	if ($organization->id) { ?>
<table class="body" cellpadding="0" cellspacing="0">
<tr><td class="title" colspan="2">Calibration Credits</td></tr>
<form name="creditForm" method="post" action="/_monitor/admin_credits">
<input type="hidden" name="organization_id" value="<?=$organization->id?>" />
<tr><td class="label">Organization</td>
	<td class="value"><?=$organization->name?></td>
</tr>
<tr><td class="label">Contact</td>
	<td class="value"><?=$organization->contact?></td>
</tr>
<tr><td class="label">Used Credits</td>
	<td class="value"><?=$organization->used_credits?></td>
</tr>
<tr><td class="label">Available Credits</td>
	<td class="value"><?=$credits?></td>
</tr>
<tr><td class="label">Additional Credits</td>
	<td class="value"><input class="value input" type="text" name="more_credits" value="0" />
	</td>
</tr>
<tr><td class="form_footer" colspan="2"><input type="button" name="btn_more" class="button" value="Add Credits" onclick="addCredits()" /></td>
</tr>
</form>
</table>
<?	} ?>