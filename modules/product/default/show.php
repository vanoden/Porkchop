<style>
	div.body {
		position: relative;
	}
	div.productEdit {
		position: absolute;
		float: right;
		top: 0px;
		left: 540px;
		z-index: 99;
	}
	a.productManual {
		float: left;
		display: block;
	}
	table.productDetailSpecs {
		width: 500px;
		background: gray;
	}
	td.label {
		font-weight: bold;
		background: #cccccc;
		margin: 1px;
	}
	td.value {
		font-weight: normal;
		background: #ffffff;
		margin: 1px;
	}
	img.productManualIcon {
		box-shadow: 2px 2px 3px #aaaaaa;
	}
</style>
<div class="body">
	<div id="name" class="label productShowName"><?=$product->name?></div>
	<div class="productEdit"><a href="/_product/edit/<?=$product->code?>">Edit</a></div>
	<div id="description" class="value productShowDescription"><?=$product->description?></div>
	<a id="productManual" class="productManualIcon" href="/_media/api?method=downloadMediaFile&code=<?=$manual->files[0]->code?>"><img src="/_media/api?method=downloadMediaFile&code=<?=$manual->icon?>" class="productManualIcon" /></a>
	<img src="/_media/api?method=downloadMediaFile&code=<?=$spectable->files[0]->code?>" class="productSpecTable" />
</div>