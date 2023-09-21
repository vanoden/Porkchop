<?php
	$site = new \Site();
	$page = $site->page();
	$page->requireAuth();

	$target_page = new \Site\Page();
	if(! $target_page->get($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) {
		$page->addError("Target Page Not Found");
	}
	else {
		$tou = $target_page->tou();
		$latest_version = $tou->latestVersion();
		if ($tou->error()) $page->error($tou->error());
		elseif (!$latest_version) $page->error('No published version of tou '.$tou->name);
		else {
			if ($GLOBALS['_SESSION_']->customer->acceptedTOU($tou->id)) {
				header("Location: /_".$target_page->module()."/".$target_page->view()."/".$target_page->index());
				exit;
			}
		}
	}

	if (!empty($_REQUEST['btn_submit'])) {
		if ($_REQUEST['btn_submit'] == 'Accept') {
			print_r("Accepted");
			if ($GLOBALS['_SESSION_']->customer->acceptTOU($_REQUEST['version_id'])) {
				header("Location: ".$target_page->uri());
				exit;
			}
			else $page->addError($GLOBALS['_SESSION_']->customer->error());
		}
		else {
			if ($GLOBALS['_SESSION_']->customer->declineTOU($_REQUEST['version_id'])) {
				header("Location: /_site/terms_of_use_declined?module=".$target_page->module()."&view=".$target_page->view()."&index=".$target_page->index());
				exit;
			}
			else $page->addError($GLOBALS['_SESSION_']->customer->error());
		}
	}