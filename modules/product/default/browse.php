<div class="body">
<?	if ($parent->code) { ?>
	<div class="productParent">
		<div class="productParentContent">
			<div class="label productParentLabel"><?=$parent->name?></div>
			<div class="value parentDescription"><?=$parent->description?></div>
<?		if (in_array('product manager',$GLOBALS['_SESSION_']->customer->roles)) { ?>
			<div class="productEdit"><a href="/_product/edit/<?=$parent->code?>">Edit</a></div>
<?		} ?>
		</div>
		<div class="productParentImages">
<?		foreach ($parent->image as $image) { ?>
			<a href="/_media/api?method=downloadMediaFile&code=<?=$image->files[0]->code ?>"><img class="productThumbnail" src="/_media/api?method=downloadMediaFile&code=<?=$image->files[0]->code ?>" /></a>
<?		} ?>
		</div>
	</div>
<?	} ?>
<?	foreach ($products as $product)
	{
		if (! $product->name) $product->name = 'Unknown';
		if ($product->type == "group")
		{
?>
	<div class="product">
		<div class="productThumbnail"><a href="<?=PATH?>/_product/browse/<?=$product->code?>"><img class="productThumbnail" src="/_media/api?method=downloadMediaFile&code=<?=$product->image[0]->files[0]->code ?>" /></a></div>
		<div class="label productLabel"><a class="productLabel" href="<?=PATH?>/_product/browse/<?=$product->code?>"><?=$product->name?></a></div>
		<div class="value productDescription"><?=$product->short_description?></div>
	</div>
<?		}
		else
		{
?>
	<div class="product">
		<div class="productThumbnail"><a href="<?=PATH?>/_product/show/<?=$product->code?>"><img class="productThumbnail" src="/_media/api?method=downloadMediaFile&code=<?=$product->image[0]->files[0]->code ?>" /></a></div>
		<div class="label productLabel"><a class="productLabel" href="<?=PATH?>/_product/show/<?=$product->code?>"><?=$product->name?></a></div>
		<div class="value productDescription"><?=$product->short_description?></div>
	</div>
<?		}
	}
	if (in_array('product manager',$GLOBALS['_SESSION_']->customer->roles)) { ?>
	<div class="product">
		<form method="post" action="/_product/add">
		<input type="hidden" name="parent_code" value="<?=$parent->code?>"
		<div class="label productLabel"><input type="submit" name="btn_add" class="button" value="Add a Product" /></div>
		</form>
	</div>
<?	} ?>
</div>