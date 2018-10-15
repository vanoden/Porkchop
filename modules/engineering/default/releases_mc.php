<?php
	$page = new \Site\Page();

	$releaselist = new \Engineering\ReleaseList();
	$releases = $releaselist->find();
?>