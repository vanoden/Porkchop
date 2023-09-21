<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('edit site pages');

	$index = $_REQUEST['index'];
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
		        if ($_REQUEST['key'] == "template" && !$page->validTemplate($_REQUEST['value'])) {
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
			        else {
						if ($editPage->setMetadata($_REQUEST['key'],$_REQUEST['value'])) {
				        	$page->success = "Metadata set";
						}
				        else {
				        	$page->addError("Error setting metadata: ".$editPage->errorString());
						}
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
        }
	}
	elseif ($editPage->getPage($_REQUEST['module'],$_REQUEST['view'],$index)) {
		$module = $editPage->module;
		$view = $editPage->view;
		$index = $editPage->index;
		if (! strlen($index)) $index = '[null]';
		$metadata = $editPage->allMetadata();
	}

    $bc_view = ucfirst($editPage->module)."::".ucfirst($editPage->view);
    if (!empty($editPage->index)) $bc_view .= "::".$editPage->index;
	$page->addBreadcrumb("Site Pages", "/_site/pages");
	if (isset($editPage->view)) {
		$page->addBreadcrumb($bc_view);
	}