<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage terms of use');

	$tou = new \Site\TermsOfUse($_REQUEST['tou_id']);

	if (!empty($_REQUEST['method'])) {
		if ($_REQUEST['method'] == 'Publish') {
			$version = new \Site\TermsOfUseVersion($_REQUEST['version_id']);
			if ($version->publish()) {
				$page->appendSuccess("Published version ".$version->date_created());
			}
			else {
				$page->addError("Error publishing version: ".$version->error());
			}
		}
	}
	$versions = $tou->versions();
	if (!is_array($versions)) {
		$page->addError("No versions found");
		$versions = [];
	}

	if ($tou->id) $page->instructions = "Update values and click Submit to update this Terms of Use record";
	else $page->instructions = "Fill out form and click Submit to create a new Terms Of Use record";
	$page->addBreadCrumb("Terms of Use","/_site/terms_of_use");
	$page->addBreadCrumb($tou->name,"/_site/term_of_use?id=".$tou->id);
	$page->addBreadCrumb("Versions");