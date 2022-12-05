<?php
	$page = new \Site\Page();
	$page->requirePrivilege('edit site pages');

	$pagelist = new \Site\PageList();
	$pages = $pagelist->find();
