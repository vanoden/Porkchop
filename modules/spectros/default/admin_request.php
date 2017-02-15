<form action="admin_request" method="POST">
<table class="body">
<tr><td class="title" colspan="2">Support Request Form</td></tr>
<?	if ($GLOBALS['_page']->error) { ?>
<tr><td class="form_error" colspan="2"><?=$GLOBALS['_page']->error?></td></tr>
<?	}
	if ($GLOBALS['_page']->success) { ?>
<tr><td class="form_success" colspan="2"><?=$GLOBALS['_page']->success?></td></tr>
<?	} ?>
<tr><td class="form_instruction" colspan="2">Fill out the form below to create a new Action Request</td></tr>
<tr><td class="label">Customer</td>
    <td class="value">
        <select name="customer_id" class="value input">
            <option value="">Select</option>
<?	foreach ($customers as $customer)
	{
		$organization = $_organization->details($customer->organization->id);
?>
            <option value="<?=$customer->id?>"><?=$customer->first_name?> <?=$customer->last_name?> (<?=$organization->name?>)</option>
<?  } ?>
        </select>
    </td>
</tr>
<tr><td class="label" valign="top">Description</td>
    <td class="value">
		<textarea name="description" class="value" style="width: 400px; height: 200px"></textarea>
	</td>
</tr>
<tr><td class="form_footer" colspan="2" style="text-align: center"><input type="submit" class="button" name="btn_submit" value="Submit"/></td></tr>
</table>
</form>