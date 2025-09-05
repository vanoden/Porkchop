<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('edit site pages');

	$index = isset($_REQUEST['index']) ? $_REQUEST['index'] : null;
	if ($index == '[null]') $index = null;
	$editPage = new \Site\Page();

	if (isset($_REQUEST['todo']) && !empty($_REQUEST['todo'])) {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid Request");
		}
		else {
			if (! $page->validModule($_REQUEST['module'])) {
				$page->addError("Invalid module name");
				$_REQUEST['index'] = null;
			}
			elseif (! $page->validView($_REQUEST['view'])) {
				$page->addError("Invalid view name");
				$_REQUEST['index'] = null;
			}
			elseif (! $page->validIndex($index)) {
				$page->addError("Invalid index");
				$_REQUEST['index'] = null;
			}
			elseif ($editPage->getPage($_REQUEST['module'],$_REQUEST['view'],$index)) {
				if (isset($_REQUEST['key']) && $_REQUEST['key'] == "template" && isset($_REQUEST['value']) && !$page->validTemplate($_REQUEST['value']) && $_REQUEST['todo'] != 'drop') {
					$page->addError("Invalid template name");
					return;
				}

				if (isset($_REQUEST['todo'])) {
					if (isset($_REQUEST['todo']) && $_REQUEST['todo'] == 'drop') {
						if ($editPage->unsetMetadata($_REQUEST['key'])) {
							$page->success = "Metadata key '".$_REQUEST['key']."' dropped";
						}
						else {
							$page->addError("Error dropping metadata: ".$editPage->errorString());
						}
					}
					else {
						if ($editPage->setMetadata($_REQUEST['key'],$_REQUEST['value'])) {
							$page->success = "Metadata key '".$_REQUEST['key']."' set to '".$_REQUEST['value']."'";
						}
						else {
							$page->addError("Error setting metadata: ".$editPage->error());
						}
					}
				}

				$module = $editPage->module;
				$view = $editPage->view;
				$index = $editPage->index;
				if (! strlen($index)) $index = '[null]';
				$metadata = $editPage->getAllMetadata();
			}
			else {
				$page->addError("Page ".$_REQUEST['module']."/".$_REQUEST['view']." index ".$_REQUEST['index']." not found");
			}
		}
	}
	elseif ($editPage->getPage($_REQUEST['module'],$_REQUEST['view'],$index)) {
		$module = $editPage->module;
		$view = $editPage->view;
		$index = $editPage->index;
		if (! strlen($index)) $index = '[null]';
		$metadata = $editPage->getAllMetadata();
	}

	$bc_view = ucfirst($editPage->module)."::".ucfirst($editPage->view);
	if (!empty($editPage->index)) $bc_view .= "::".$editPage->index;
	$page->addBreadcrumb("Site Pages", "/_site/pages");
	if (isset($editPage->view)) {
		$page->addBreadcrumb($bc_view);
	}