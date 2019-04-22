<?php
	$page = new \Site\Page();
	$page->requireRole('engineering user');
	$releaselist = new \Engineering\ReleaseList();
	$releases = $releaselist->find();
