<form action="request" method="POST">
<table class="body">
<tr><td class="title" colspan="2">Support Request Form</td></tr>
<?php	if ($GLOBALS['_page']->error) { ?>
<tr><td class="form_error" colspan="2"><?=$GLOBALS['_page']->error?></td></tr>
<?php	}
	if ($GLOBALS['_page']->success) { ?>
<tr><td class="form_success" colspan="2"><?=$GLOBALS['_page']->success?></td></tr>
<?php	} ?>
<tr><td class="form_instruction">Please provide the best description of your problem below.  Include any serial numbers and contact information.</td></tr>
<tr><td class="value">
		<textarea name="description" class="value" style="width: 400px; height: 200px"></textarea>
	</td>
</tr>
<tr><td class="form_footer"><input type="submit" class="button" name="btn_submit" value="Submit"/></td></tr>
</table>
</form>
