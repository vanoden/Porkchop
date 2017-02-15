<?  if (! role('monitor admin'))
    {
        print "<span class=\"form_error\">You are not authorized for this view!</span>";
        return;
    }
?>
<script language="Javascript">
	function selectOrganization(elem)
	{
		calibrationForm.submit();
		return false;
	}
</script>
<table class="body" style="width: 600px;">
<form method="post" name="calibrationForm" action="/_spectros/admin_credits">
<tr><td class="title" colspan="2">Calibration Credits</td></tr>
<?	if ($GLOBALS['_page']->error) { ?>
<tr><td class="form_error" colspan="2"><?=$GLOBALS['_page']->error?></td></tr>
<?	} ?>
<?	if ($GLOBALS['_page']->success) { ?>
<tr><td class="form_success" colspan="2"><?=$GLOBALS['_page']->success?></td></tr>
<?	} ?>
<tr><td class="label" colspan="2">Organization</td></tr>
<tr><td class="value" colspan="2">
		<select name="organization_id" class="value input" onchange="selectOrganization(this);">
			<option value="">Select</option>
<?	foreach ($organizations as $organization) { ?>
			<option value="<?=$organization->id?>"<? if ($organization->id == $_REQUEST['organization_id']) print " selected";?>><?=$organization->name?></option>
<?	} ?>
		</select>
	</td>
</tr>
<?	if ($_REQUEST['organization_id']) { ?>
<tr><td class="label">Current</td>
	<td class="value"><?=print_r($credits,true)?></td>
</tr>
<tr><td class="label">Add</td>
	<td class="value"><input type="text" name="add_credits" value="0" class="value input" /></td>
</tr>
<tr><td class="label" colspan="2" style="text-align: center">
		<input type="submit" name="btn_submit" value="Submit" class="button"/>
	</td>
</tr>
<?	} ?>
</form>
</table>
