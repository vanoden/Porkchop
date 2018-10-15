<?  if (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin'))
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
<h2>Calibration Credits</h2>
<?	if ($page->error) { ?>
<div class="form_error" colspan="2"><?=$page->error?></div>
<?	} ?>
<?	if ($page->success) { ?>
<div class="form_success" colspan="2"><?=$page->success?></div>
<?	} ?>
<table class="body">
<form method="post" name="calibrationForm" action="/_spectros/admin_credits">
<tr><th class="label" colspan="2">Organization</th></tr>
<tr><td class="value" colspan="2">
		<select name="organization_id" class="value input" onchange="selectOrganization(this);">
			<option value="">Select</option>
<?	foreach ($organizations as $organization) { ?>
			<option value="<?=$organization->id?>"<? if (isset($_REQUEST['organization_id']) && $organization->id == $_REQUEST['organization_id']) print " selected";?>><?=$organization->name?></option>
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
