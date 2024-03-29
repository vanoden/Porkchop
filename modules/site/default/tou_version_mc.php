<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage terms of use');

	$version = new \Site\TermsOfUseVersion();
	if (!empty($_REQUEST['id'])) {
		$version->load($_REQUEST['id']);
	}

	if (!empty($_REQUEST['tou_id'])) {
		$tou = new \Site\TermsOfUse($_REQUEST['tou_id']);
	}
	else {
		$tou = $version->termsOfUse();
	}

	if (!empty($_REQUEST['btn_submit'])) {
		$page->appendSuccess("Form submitted");
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
            $page->addError("Invalid Request");
	    }
		elseif (empty($tou->id)) {
			$page->addError("No terms of use selected [tou_id=".$_REQUEST['tou_id']."]");
		}
		elseif (!empty($_REQUEST['content']) && ! $version->validContent($_REQUEST['content'])) {
			$page->addError("Invalid content");
		}
		else {
			$parameters = array('content' => $_REQUEST['content']);
			if ($_REQUEST['id']) {
				$version->update($parameters);
				$page->appendSuccess("Updated version ".$version->date_created());
			}
			else {
				$version = $tou->addVersion($parameters);
				if ($tou->error()) $page->addError($tou->error());
				else $page->appendSuccess("Added Version ".$version->date_created());
			}
		}
	}

	$page->title('Terms Of Use Version');
	$page->instructions = "Add some stuff here";
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

	$tou_id = (!empty($tou->id)) ? $tou->id : 0;
	$tou_name = (!empty($tou->name)) ? $tou->name : "";
	$message_id = (!empty($message->id)) ? $message->id : 0;