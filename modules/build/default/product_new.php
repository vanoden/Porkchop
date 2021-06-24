<h2>Build Product</h2>
<?php	if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?php	} ?>
<form action="/_build/product_new" method="post">
<div class="container">
	<span class="label">Name</span>
	<input type="text" name="name" class="value input" value="<?=$_REQUEST['name']?>" />
</div>
<div class="container">
	<span class="label">Architecture</span>
	<input type="text" name="architecture" class="value input" value="<?=$_REQUEST['architecture']?>" />
</div>
<div class="container">
	<span class="label">Description</span>
	<textarea name="description" class="value"><?=$_REQUEST['description']?></textarea>
</div>
<div class="container">
	<span class="label">Major Version</span>
	<input type="text" name="major_version" class="value input" value="<?=$_REQUEST['major_version']?>" />
</div>
<div class="container">
	<span class="label">Minor Number</span>
	<input type="text" name="minor_version" class="value input" value="<?=$_REQUEST['minor_version']?>" />
</div>
<div class="container">
	<span class="label">Workspace</span>
	<input type="text" name="workspace" class="value input" value="<?=$_REQUEST['workspace']?>" />
</div>
<div class="form_footer">
	<input type="submit" name="btn_submit" class="button" value="Add Product" />
</div>
</form>
