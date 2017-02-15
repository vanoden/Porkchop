<style>
	td.label {
		width: 150px;
		vertical-align: top;
	}
	.input {
		margin-bottom: 12px;
		width: 250px;
	}
	textarea.input {
		width: 400px;
		height: 200px;
	}
</style>
<h2>Request Support</h2>
<form name="supportRequest" method="post" action="/_support/request">
<table class="body">
<?	if ($GLOBALS['_page']->error) { ?>
<tr><td class="form_error" colspan="2"><?=$GLOBALS['_page']->error?></td></tr>
<?	} ?>
<tr><td class="label">Serial Number</td>
	<td class="value"><input type="text" name="serial_number" class="value input" /></td>
	</td>
</tr>
<tr><td class="label">Problem Type</td>
	<td class="value"><select name="type" class="value input">
		<option value="">Select</option>
		<option value="web portal">Web Portal</option>
		<option value="gas monitor">Gas Monitor</option>
		<option value="gas monitor">Billing and Account</option>
	</td>
</tr>
<tr><td class="label">Describe Problem</td>
	<td class="value"><textarea name="description" class="value input"></textarea>
</tr>
<tr><td class="form_footer" colspan="2" style="text-align: center">
		<input type="submit" name="btn_submit" class="button" value="Submit" />
	</td>
</tr>
</table>
</form>