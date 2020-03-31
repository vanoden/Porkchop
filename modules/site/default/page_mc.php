<?php
	$page = new \Site\Page();
	$page->requireRole('administrator');

	$index = $_REQUEST['index'];
	if ($index == '[null]') $index = null;

	$editPage = new \Site\Page();
	if ($editPage->get($_REQUEST['module'],$_REQUEST['view'],$index)) {
		if ($_REQUEST['todo']) {
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
		$metadata = $editPage->metadata();
	}
	else {
		$page->addError("Page ".$_REQUEST['module']."/".$_REQUEST['view']." index ".$_REQUEST['index']." not found");
	}
