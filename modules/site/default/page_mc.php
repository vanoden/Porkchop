<?php
	$page = new \Site\Page();
	$page->requirePrivilege('edit site pages');

	$index = $_REQUEST['index'];
	if ($index == '[null]') $index = null;

	$editPage = new \Site\Page();
	if ($editPage->get($_REQUEST['module'],$_REQUEST['view'],$index)) {
		if ($_REQUEST['key'] == "template" && !preg_match('/^[\w\-\.\_]+$/',$_REQUEST['value'])) {
			$page->addError("Invalid template name");
			return;
		}
		if (isset($_REQUEST['todo'])) {
			if ($_REQUEST['todo'] == 'drop') {
				if ($editPage->unsetMetadata($_REQUEST['key'])) {
					$page->success = "Metadata dropped";
				}
				else {
					$page->addError("Error dropping metadata: ".$editPage->errorString());
				}
			}
			elseif ($editPage->setMetadata($_REQUEST['key'],$_REQUEST['value'])) {
				$page->success = "Metadata set";
			}
			else {
				$page->addError("Error setting metadata: ".$editPage->errorString());
			}
		}

		$module = $editPage->module;
		$view = $editPage->view;
		$index = $editPage->index;
		if (! strlen($index)) $index = '[null]';
		$metadata = $editPage->allMetadata();
	}
	else {
		$page->addError("Page ".$_REQUEST['module']."/".$_REQUEST['view']." index ".$_REQUEST['index']." not found");
	}
