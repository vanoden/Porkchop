<?php
	$page = new \Site\Page();
	$page->requirePrivilege('edit site pages');

	if ($_REQUEST['btn_submit']) {
		foreach($_REQUEST['tou_id'] as $page_id => $tou_id) {
			if (!isset($tou_id) || !is_numeric($tou_id)) $tou_id = '0';
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
		}
	}

	$pagelist = new \Site\PageList();
	$pages = $pagelist->find();

	$touList = new \Site\TermsOfUseList();
	$terms_of_use = $touList->find();