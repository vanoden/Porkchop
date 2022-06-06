<div class="body">
<?php	foreach ($items as $item) { ?>
	<div class="mediaItem">
	<?php	if ($GLOBALS['_SESSION_']->customer->can('manage media files')) { ?>
		<a class="mediaItemEdit" href="/_media/edit/<?=$item->code?>">Edit</a>
	<?php	} ?>
		<img class="mediaItemIcon" src="<?=$item->icon?>" />
		<div class="mediaItemName"><?=$item->name?></div>
		<div class="mediaItemDate"><?=date("F jS, Y",$item->files[0]->timestamp)?></div>
		<div class="mediaItemLink"><a href="/_media/api?method=downloadMediaFile&code=<?=$item->files[0]->code?>"><?=$item->files[0]->original_file?></a></div>
	</div>
<?php	} ?>
</div>
