<?php
	$page = new \Site\Page();
	$page->requirePrivilege('browse engineering objects');
	$releaselist = new \Engineering\ReleaseList();
	$releases = $releaselist->find();
