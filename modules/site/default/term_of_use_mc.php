<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage terms of use');

	$tou = new \Site\TermsOfUse();
	if (isset($_REQUEST['id'])) $tou = new \Site\TermsOfUse($_REQUEST['id']);

	if (!empty($_REQUEST['btn_submit'])) {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
			$page->addError("Invalid Token");
		}
		elseif ($tou->id)
			if (! $tou->update(array('name' => $_REQUEST['name'], 'description' => $_REQUEST['description'])))
				$page->addError("Could not update Terms of Use: ".$tou->error());
			else
				$page->appendSuccess("Terms of Use record updated");
		else
			if (! $tou->add(array('name' => $_REQUEST['name'], 'description' => $_REQUEST['description'])))
				$page->addError("Could not add Terms of Use: ".$tou->error());
			else
				$page->appendSuccess("Terms of Use record created");
	}

	if ($tou->id) $page->instructions = "Update values and click Submit to update this Terms of Use record";
	else $page->instructions = "Fill out form and click Submit to create a new Terms Of Use record";
	$page->addBreadCrumb("Terms of Use","/_site/terms_of_use");
	if ($tou->id) $page->addBreadCrumb($tou->name);
	else $page->addBreadCrumb("New Terms of Use");