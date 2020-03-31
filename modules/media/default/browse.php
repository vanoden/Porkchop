<style>
	div.mediaItem {
		position: relative;
	}
	div.mediaItemEdit {
		position: absolute;
		float: right;
		top: 0px;
		left: 300px;
	}
</style>
<div class="body">
<?php	if (in_array('media manager',$GLOBALS['_SESSION_']->customer->roles)) { ?>
	<div class="mediaItemAdd"><a href="/_media/edit/">Add</a></div>
<?php	} ?>
<?php	foreach ($items as $item) { ?>
	<div class="mediaItem">
		<img class="mediaItemIcon" src="<?=$item->icon?>" />
		<div class="mediaItemName"><?=$item->name?></div>
		<div class="mediaItemDate"><?=date("F jS, Y",$item->files[0]->timestamp)?></div>
		<div class="mediaItemDescription"><?=$item->description?></div>
		<div class="mediaItemLink"><a href="/_media/api?method=downloadMediaFile&code=<?=$item->files[0]->code?>"><?=$item->files[0]->original_file?></a></div>
<?php	if (in_array('media manager',$GLOBALS['_SESSION_']->customer->roles)) { ?>
		<div class="mediaItemEdit"><a href="/_media/edit/<?=$item->code?>">Edit</a></div>
<?php	} ?>
	</div>
<?php	} ?>
</div>
