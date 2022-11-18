<span class="title">Product</span>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<div class="body">
    <?php  if ($parent->code) { ?>
	    <div class="productParent">
		    <div class="productParentContent">
			    <div class="label productParentLabel"><?=$parent->name?></div>
			    <div class="value parentDescription"><?=$parent->description?></div>
                <?php if ($GLOBALS['_SESSION_']->customer->can('manage products')) { ?>
			        <div class="productEdit"><a href="/_product/edit/<?=$parent->code?>">Edit</a></div>
                <?php } ?>
		    </div>
		    <div class="productParentImages">
            <?php foreach ($parent->image as $image) { ?>
		        	<a href="/_media/api?method=downloadMediaFile&code=<?=$image->files[0]->code ?>"><img class="productThumbnail" src="/_media/api?method=downloadMediaFile&code=<?=$image->files[0]->code ?>" /></a>
            <?php } ?>
		    </div>
	    </div>
    <?php } 
     foreach ($products as $product) {
		    if (! $product->name) $product->name = 'Unknown';
		    if ($product->type == "group") { ?>
	            <div class="product">
		            <div class="productThumbnail"><a href="<?=PATH?>/_product/browse/<?=$product->code?>"><img class="productThumbnail" src="/_media/api?method=downloadMediaFile&code=<?=$product->image[0]->files[0]->code ?>" /></a></div>
		            <div class="label productLabel"><a class="productLabel" href="<?=PATH?>/_product/browse/<?=$product->code?>"><?=$product->name?></a></div>
		            <div class="value productDescription"><?=$product->short_description?></div>
	            </div>
        <?php } else { ?>
	            <div class="product">
		            <div class="productThumbnail"><a href="<?=PATH?>/_product/show/<?=$product->code?>"><img class="productThumbnail" src="/_media/api?method=downloadMediaFile&code=<?=$product->image[0]->files[0]->code ?>" /></a></div>
		            <div class="label productLabel"><a class="productLabel" href="<?=PATH?>/_product/show/<?=$product->code?>"><?=$product->name?></a></div>
		            <div class="value productDescription"><?=$product->short_description?></div>
	            </div>
    <?php }
	}
	if ($GLOBALS['_SESSION_']->customer->can('manage products')) { ?>
	    <div class="product">
		    <form method="post" action="/_product/add">
		        <input type="hidden" name="parent_code" value="<?=$parent->code?>">
		        <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		        <div class="label productLabel"><input type="submit" name="btn_add" class="button" value="Add a Product" /></div>
		    </form>
	    </div>
<?php } ?>
</div>
