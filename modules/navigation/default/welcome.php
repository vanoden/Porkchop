<div id="welcome_menu">
<?php	foreach ($items as $item) { ?>
	<div class="welcome_menu_item">
		<a class="label welcome_menu_label" href="<?=$item->target?>" alt="<?=$item->alt?>"><?=$item->title?></a>
		<span class="value welcome_menu_description"><?=strip_tags($item->description)?></span>
	</div>
<?php	} ?>
</div>
