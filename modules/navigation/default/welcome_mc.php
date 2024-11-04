<?php
	$page = new \Site\Page();
	$menu = new \Site\Navigation\Menu();
	$menu->get('welcome');
	$items = $menu->items();