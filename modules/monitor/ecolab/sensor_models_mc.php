<?php
	$page = new \Site\Page();
	$page->requireRole("monitor admin");

	$modellist = new \Monitor\Sensor\ModelList();
	$models = $modellist->find();
