<style>
	span.label {
		float: left;
		display: block;
		width: 160px;
		clear: left;
	}
	textarea.input {
		height: 75px;
		width: 300px;
	}
	div.editImages {
		clear: both;
	}
	input.input {
		width: 300px;
	}
	div.editItemImage {
		float: left;
		width: 126px;
		height: 126px;
		border: 1px solid gray;
		overflow: hidden;
	}
	img.editItemThumbnail {
		width: 120px;
		margin: 3px;
		padding: 0px;
	}
</style>
<div class="title">Add Product</div>
<?	if ($page->errorCount() > 0) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	} ?>
<form name="productEdit" method="post" action="/_product/edit">
<input type="hidden" name="parent_code" id="parent_code" value="<?=$_REQUEST["parent_code"]?>" />
<div class="body">
	<div class="editItem" id="itemCode">
		<span class="label">Code</span>
		<input type="text" name="code" class="value input" />
	</div>
	<div class="editItem" id="itemType">
		<span class="label">Type</span>
		<select name="type" class="value input">
			<option value="">Select</option>
			<option value="group">Group</option>
			<option value="kit">Kit</option>
			<option value="inventory">Inventory</option>
			<option value="unique">Unique</option>
			<option value="note">Note</option>
		</select>
	</div>
	<div class="editItem" id="itemName">
		<span class="label">Name</span>
		<input type="text" class="value input" name="name" id="name" />
	</div>
	<div class="editItem" id="itemStatus">
		<span class="label">Status</span>
		<select name="status" class="value input">
			<option value="">Select</option>
			<option value="ACTIVE">ACTIVE</option>
			<option value="HIDDEN">HIDDEN</option>
			<option value="DELETED">DELETED</option>
		</select>
	</div>
	<div class="editItem" id="itemShortDescription">
		<span class="label">Short Description</span>
		<textarea class="value input" name="short_description" id="short_description"></textarea>
	</div>
	<div class="editItem" id="itemDescription">
		<span class="label">Description</span>
		<textarea class="value input" name="description" id="description"></textarea>
	</div>
	<div class="editItem">
		<span class="label">Empirical Formula</span>
		<input type="text" class="value input" name="empirical_formula" id="empirical_formula" value="" />
	</div>
	<div class="editItem">
		<span class="label">Sensitivity</span>
		<input type="text" class="value input" name="sensitivity" id="sensitivity" value="" />
	</div>
	<div class="editItem">
		<span class="label">Measure Range</span>
		<input type="text" class="value input" name="measure_range" id="measure_range" value="" />
	</div>
	<div class="editItem">
		<span class="label">Accuracy</span>
		<input type="text" class="value input" name="accuracy" id="accuracy" value="" />
	</div>
	<div class="editImages" id="itemImages">
		<span class="label">Images</span>
<?	foreach($item->image as $image) { ?>
		<div class="editItemImage"><img class="editItemThumbnail" src="/_media/api?method=downloadMediaFile&code=<?=$image->files[0]->code ?>" /></div>
<?	} ?>
		<div class="editItemImage"><input type="button" name="addImageButton" value="Add Image" onclick="selectImage()" /></div>
	</div>
	<hr style="width: 900px; clear: both; visibility: hidden;" />
	<div class="editSubmit">
		<input type="submit" class="button" value="Add" name="submit" id="submit"/>
	</div>
</div>
</form>
