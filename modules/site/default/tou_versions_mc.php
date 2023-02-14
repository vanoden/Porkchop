<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage terms of use');

	$tou = new \Site\TermsOfUse($_REQUEST['tou_id']);

	$versions = $tou->versions();

	if ($tou->id) $page->instructions = "Update values and click Submit to update this Terms of Use record";
	else $page->instructions = "Fill out form and click Submit to create a new Terms Of Use record";
	$page->addBreadCrumb("Terms of Use","/_site/terms_of_use");
	$page->addBreadCrumb($tou->name,"/_site/term_of_use?id=".$tou->id);
	$page->addBreadCrumb("Versions");