<?php
	$page = new \Site\Page();
	$page->requireRole('register manager');

	$roleList = new \Register\RoleList();
	$roles = $roleList->find();
?>
