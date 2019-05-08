<?php
	$page = new \Site\Page();

	$menu = new \Navigation\Menu();
	$menu->get('welcome');

	$items = $menu->items();
?>