<style>
	.welcome_menu_item {
		margin-bottom: 10px;
	}
	.welcome_menu_label {
		display: block;
		clear: both;
	}
</style>
<div id="welcome_menu">
<?	foreach ($menus as $menu) { ?>
	<div class="welcome_menu_item">
		<a class="label welcome_menu_label" href="<?=$menu->target?>"><?=$menu->label?></a>
		<span class="value welcome_menu_description"><?=$menu->description?></span>
	</div>
<?	} ?>
</div>