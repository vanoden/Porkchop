<div class="body">
<?	foreach ($items as $item) { ?>
	<div class="mediaItem">
	<?	if (role('media manager')) { ?>
		<a class="mediaItemEdit" href="/_media/edit/<?=$item->code?>">Edit</a>
	<?	} ?>
		<img class="mediaItemIcon" src="<?=$item->icon?>" />
		<div class="mediaItemName"><?=$item->name?></div>
		<div class="mediaItemDate"><?=date("F jS, Y",$item->files[0]->timestamp)?></div>
		<div class="mediaItemLink"><a href="/_media/api?method=downloadMediaFile&code=<?=$item->files[0]->code?>"><?=$item->files[0]->original_file?></a></div>
	</div>
<?	} ?>
</div>