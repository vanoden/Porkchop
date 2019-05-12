<?	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	} ?>
<form action="/_barcode/read" method="post" enctype="multipart/form-data">
	Select image to upload:
	<input type="file" name="barcode" id="barcode">
	<input type="submit" value="Upload Image" name="submit">
</form>