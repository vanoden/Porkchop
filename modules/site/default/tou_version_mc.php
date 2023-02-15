<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage terms of use');

	$version = new \Site\TermsOfUseVersion();
	if ($_REQUEST['version_id']) {
		$version->load($_REQUEST['version_id']);
	}

	if ($_REQUEST['tou_id']) {
		$tou = new \Site\TermsOfUse($_REQUEST['tou_id']);
	}
	else {
		$tou = $version->terms_of_use();
	}

	if ($_REQUEST['btn_submit']) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
            $page->addError("Invalid Request");
	    }
		elseif (!empty($_REQUEST['content']) && ! $version->validContent($_REQUEST['content'])) {
			$page->addError("Invalid content");
		}
		else {
			$parameters = array('content' => $_REQUEST['content']);
			if ($_REQUEST['id']) {
				$version->update($parameters);
				$page->appendSuccess("Updated version ".$version->number());
			}
			else {
				$version = $tou->addVersion($parameters);
				$page->appendSuccess("Added Version ".$version->number());
			}
		}
	}

	$page->title('Terms Of Use Version');
	$page->instructions("Add some stuff here");
	$page->addBreadCrumb("Terms of Use","/_site/terms_of_use");
	$page->addBreadCrumb($tou->name,"/_site/term_of_use?id=".$tou->id);
	$page->addBreadCrumb("Versions","/_site/tou_versions?tou_id=".$tou->id);
	if ($version->id) $page->addBreadCrumb($version->number());
	else $page->addBreadCrumb("New Version");

	if (empty($version->id)) {
		$version->status = 'NEW';
		$version_number = "";
	}
	else {
		$version_number = $version->number();
	}