<?php
	$page = new \Site\Page();
	$page->requireRole('monitor manager');

	$dashboardList = new \Monitor\DashboardList();
	$dashboards = $dashboardList->find();