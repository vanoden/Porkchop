<?php
	$page = new \Site\Page();
	$page->requireAuth();

	$menu = new \Site\Navigation\Menu();
	$menu->get('welcome');
	$items = $menu->items();
	if ($menu->error()) $page->addError($menu->error());
