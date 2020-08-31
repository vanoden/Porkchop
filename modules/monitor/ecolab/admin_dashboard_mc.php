<?php
	$page = new \Site\Page();
	$page->requireRole('monitor admin');

	$dashboard = new \Monitor\Dashboard($_REQUEST['id']);

	if (isset($_REQUEST['btn_submit'])) {
		$parameters = array(
			'name'	=> $_REQUEST['name'],
			'template'	=> $_REQUEST['template'],
			'status'	=> $_REQUEST['status']
		);
		if (! $dashboard->update($parameters)) {
			$page->addError($dashboard->error());
		}
	}
	if ($_REQUEST['todo']) {
		if ($dashboard->getMetadata($_REQUEST['key'])) {
			if ($_REQUEST['todo'] == 'drop') {
				if ($dashboard->unsetMetadata($_REQUEST['key'])) {
					$page->success = "Metadata dropped";
				}
				else {
					$page->addError("Error dropping metadata: ".$dashboard->error());
				}
			}
			elseif ($dashboard->setMetadata($_REQUEST['key'],$_REQUEST['value'],$_REQUEST['type'])) {
				$page->success = "Metadata set";
			}
			else {
				$page->addError("Error setting metadata: ".$dashboard->error());
			}
		}
	}

	$dashboard = new \Monitor\Dashboard($_REQUEST['id']);
	$metadata = $dashboard->getAllMetadata();
