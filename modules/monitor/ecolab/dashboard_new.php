<h2>New Dashboard</h2>
<?php	if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?php	} ?>
<form method="post" action="/_monitor/dashboard_new">
<div class="container">
	<span class="label">Name</span>
	<input type="text" name="name" class="value input" value="<?=$_REQUEST['name']?>" />
</div>
<div class="container">
	<span class="label">Template</span>
	<input type="text" name="template" class="value input" value="<?=$_REQUEST['template']?>" />
</div>
<div class="container">
	<span class="label">Status</span>
	<select name="status" class="value input">
		<option value="NEW"<?php	if ($_REQUEST['status'] == 'NEW') print " selected"; ?>>New</option>
		<option value="HIDDEN"<?php	if ($_REQUEST['status'] == 'HIDDEN') print " selected"; ?>>Hidden</option>
		<option value="TEST"<?php	if ($_REQUEST['status'] == 'TEST') print " selected"; ?>>Test</option>
		<option value="PUBLISHED"<?php	if ($_REQUEST['status'] == 'PUBLISHED') print " selected"; ?>>Published</option>
	</select>
</div>
<div class="form_footer">
	<input type="submit" name="btn_submit" class="button" value="Create Dashboard" />
</div>
</form>
