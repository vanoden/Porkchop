
<div class="body">
	<div id="name" class="label productShowName"><?=$product->name?></div>
	<div class="productEdit"><a href="/_product/edit/<?=$product->code?>">Edit</a></div>
	<div id="description" class="value productShowDescription"><?=strip_tags($product->description)?></div>
	<a id="productManual" class="productManualIcon" href="/_media/api?method=downloadMediaFile&code=<?=$manual->files[0]->code?>"><img src="/_media/api?method=downloadMediaFile&code=<?=$manual->icon?>" class="productManualIcon" /></a>
	<img src="/_media/api?method=downloadMediaFile&code=<?=$spectable->files[0]->code?>" class="productSpecTable" />
</div>