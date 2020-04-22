<div class="body">
<?php	foreach ($items as $item) { ?>
	<div class="mediaItem">
		<img class="mediaItemIcon" src="/images/icons/pdf.png" />
		<div class="mediaItemName"><?=$item->name?></div>
		<div class="mediaItemDate"><?=date("F jS, Y",$item->files[0]->timestamp)?></div>
		<div class="mediaItemLink"><a href="/_media/api?method=downloadMediaFile&code=<?=$item->files[0]->code?>"><?=$item->files[0]->original_file?></a></div>
	</div>
<?php	} ?>
</div>
