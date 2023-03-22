<?php
	$site = new \Site();
	$page = $site->page();

	$target_page = new \Site\Page();
	if(! $target_page->get($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) {
		$page->addError("Target Page Not Found");
	}
	else {
		$tou = $target_page->tou();
		$latest_version = $tou->latestVersion();
		if ($tou->error()) $page->error($tou->error());
		elseif (!$latest_version) $page->error('No published version of tou '.$tou->title);
		else {
			if ($GLOBALS['_SESSION_']->customer->acceptedTOU($tou_id)) {
				header("Location: /_".$this->module()."/".$this->view()."/".$this->index());
				exit;
			}
		}
	}

	if (!empty($_REQUEST['btn_submit'])) {
		if ($_REQUEST['btn_submit'] == 'Accept') {
			print_r("Accepted");
			if ($GLOBALS['_SESSION_']->customer->acceptTOU($_REQUEST['version_id'])) {
				header("Location: /_".$this->module()."/".$this->view()."/".$this->index());
				exit;
			}
			else $page->addError($GLOBALS['_SESSION_']->customer->error());
		}
		else {
			if ($GLOBALS['_SESSION_']->customer->declineTOU($_REQUEST['version_id'])) {
				header("Location: /_site/terms_of_use_declined?module=".$this->module()."&view=".$this->view()."&index=".$this->index());
				exit;
			}
			else $page->addError($GLOBALS['_SESSION_']->customer->error());
		}
	}