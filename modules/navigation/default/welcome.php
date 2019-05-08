<div id="welcome_menu">
<?	foreach ($items as $item) { ?>
	<div class="welcome_menu_item">
		<a class="label welcome_menu_label" href="<?=$item->target?>" alt="<?=$item->alt?>"><?=$item->title?></a>
		<span class="value welcome_menu_description"><?=$item->description?></span>
	</div>
<?	} ?>
</div>