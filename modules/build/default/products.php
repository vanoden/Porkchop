<h2>Build Product</h2>
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Name</div>
		<div class="tableCell">Major Version</div>
		<div class="tableCell">Minor Version</div>
		<div class="tableCell">Workspace</div>
		<div class="tableCell">Last Version</div>
	</div>
<?php	foreach ($products as $product) {
	$last_version = $product->lastVersion();
?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_build/product?id=<?=$product->id?>"><?=$product->name?></a></div>
		<div class="tableCell"><?=$product->major_version?></div>
		<div class="tableCell"><?=$product->minor_version?></div>
		<div class="tableCell"><?=$product->workspace?></div>
		<div class="tableCell"><a href="/_build/version?id=<?=$last_version->id?>"><?=$last_version->number?></a></div>
	</div>
<?php	} ?>
</div>
<div class="form_footer">
	<form action="/_build/product_new" method="post">
	<input type="submit" name="btn_add" value="Add Product" class="button">
	</form>
</div>
