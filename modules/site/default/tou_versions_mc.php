<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage terms of use');

	$tou = new \Site\TermsOfUse($_REQUEST['tou_id']);

	if (!empty($_REQUEST['method'])) {
		if ($_REQUEST['method'] == 'publish') {
			$version = new \Site\TermsOfUseVersion($_REQUEST['id']);
			if ($version->publish()) {
				$page->appendSuccess("Published version ".$version->date_created());
			}
			else {
				$page->addError("Error publishing version: ".$version->error());
			}
		}
		elseif ($_REQUEST['method'] == 'retract') {
			$version = new \Site\TermsOfUseVersion($_REQUEST['id']);
			if ($version->retract()) {
				$page->appendSuccess("Retracted version ".$version->date_created());
			}
			else {
				$page->addError("Error retracting version: ".$version->error());
			}
		}
	}
	$versions = $tou->versions();
	if ($tou->error()) {
		$page->addError($tou->error());
	}
	elseif (!empty($version) && count($version) < 0) {
		$page->addError("No versions found");
	}

	if ($tou->id) $page->instructions = "Update values and click Submit to update this Terms of Use record";
	else $page->instructions = "Fill out form and click Submit to create a new Terms Of Use record";
	$page->addBreadCrumb("Terms of Use","/_site/terms_of_use");
	$page->addBreadCrumb($tou->name,"/_site/term_of_use?id=".$tou->id);
	$page->addBreadCrumb("Versions");
