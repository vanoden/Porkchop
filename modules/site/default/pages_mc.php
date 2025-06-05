<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('edit site pages');

	$pagelist = new \Site\PageList();
	$pages = $pagelist->find();

	if ($_REQUEST['button_submit']) {
		foreach ($pages as $edit_page) {
			$page_id = $edit_page->id;
			if (isset($_REQUEST['tou_id'][$page_id]) && $_REQUEST['tou_id'][$page_id] > 0) {
				$tou_id = $_REQUEST['tou_id'][$page_id];
			} else {
				$tou_id = 0;
			}
			if (isset($_REQUEST['sitemap'][$page_id]) && is_numeric($_REQUEST['sitemap'][$page_id]) && $_REQUEST["sitemap"][$page_id] == 1) $sitemap = true;
			else $sitemap = false;

			$edit_page = new \Site\Page($page_id);
			if ($edit_page->tou_id != $tou_id) {
				if ($tou_id < 1) {
					$page->appendSuccess("Removed TOU requirement for /_".$edit_page->module()."/".$edit_page->view()."/".$edit_page->index);
				}
				else {
					$tou = new \Site\TermsOfUse($tou_id);
					$page->appendSuccess("Set terms of use for /_".$edit_page->module()."/".$edit_page->view()."/".$edit_page->index." to ".$tou->name);
				}
				$edit_page->update(array('tou_id' => $tou_id));
			}

			if ($edit_page->sitemap != $sitemap) {
				$edit_page->update(array('sitemap' => $sitemap));
				if ($sitemap == true) 
					$page->appendSuccess("Added /_".$edit_page->module()."/".$edit_page->view()."/".$edit_page->index." to sitemap");
				else
					$page->appendSuccess("Removed /_".$edit_page->module()."/".$edit_page->view()."/".$edit_page->index." from sitemap");

			}
		}
	}

	$pages = $pagelist->find();

	$touList = new \Site\TermsOfUseList();
	$terms_of_use = $touList->find();

	$page->title("Site Pages");
