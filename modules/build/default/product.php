<script language="Javascript">
	function goVersions() {
		window.location.href = "/_build/versions?product_id=<?=$product->id?>";
	}
</script>
<h2>Build Product</h2>
<?	if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	} elseif ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?	} ?>
<form action="/_build/product" method="post">
<input type="hidden" name="id" value="<?=$product->id?>" />
<div class="container">
	<span class="label">Name</span>
	<span class="value"><?=$product->name?></span>
</div>
<div class="container">
	<span class="label">Architecture</span>
	<span class="value"><?=$product->architecture?></span>
</div>
<div class="container">
	<span class="label">Description</span>
	<textarea name="description" class="value"><?=$product->description?></textarea>
</div>
<div class="container">
	<span class="label">Major Version</span>
	<input type="text" name="major_version" class="value input" value="<?=$product->major_version?>" />
</div>
<div class="container">
	<span class="label">Minor Number</span>
	<input type="text" name="minor_version" class="value input" value="<?=$product->minor_version?>" />
</div>
<div class="container">
	<span class="label">Workspace</span>
	<input type="text" name="workspace" class="value input" value="<?=$product->workspace?>" />
</div>
<div class="form_footer">
	<input type="submit" name="btn_submit" class="button" value="Update Product" />
	<input type="button" name="btn_versions" class="button" value="Product Versions" onclick="goVersions()" />
</div>
</form>