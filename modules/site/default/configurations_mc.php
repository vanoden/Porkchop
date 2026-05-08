<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('configure site');

	// Create a temporary Configuration object for validation
	$validationConfig = new \Site\Configuration();
	
	if (!empty($_REQUEST['key']) && $validationConfig->validKey($_REQUEST['key'])) {
		$siteConfiguration = new \Site\Configuration($_REQUEST['key']);

		if (!empty($_REQUEST['todo'])) {
			print_r("Todo: " . $_REQUEST['todo']);
			if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
				$page->addError("Invalid Request");
			}
			elseif (!empty($_REQUEST['key']) && ! $siteConfiguration->validKey($_REQUEST['key'])) {
				$page->addError("Invalid key");
			}
			elseif (!empty($_REQUEST['value']) && ! $siteConfiguration->validValue($_REQUEST['value'])) {
				$page->addError("Invalid value");
			}
			elseif ($siteConfiguration->readOnly) {
				$page->addError("Configuration is read-only and cannot be modified");
			}
			else {
				switch ($_REQUEST['todo']) {
					case 'drop':
						$siteConfiguration->delete();
						$page->success = 'Configuration Deleted';
						break;

					case 'add':
						$siteConfiguration->key = $_REQUEST['key'];
						$siteConfiguration->set($_REQUEST['value']);
						$page->success = 'Configuration Added';
						break;
						
					case 'update':
						$siteConfiguration->set($_REQUEST['value']);
						$page->success = 'Configuration Update';
						break;
				}
			}
		}
	}
	elseif (!empty($_REQUEST['key'])) {
		$page->addError("Invalid configuration key");
	}

	$siteConfigurations = new \Site\ConfigurationList();
	$configuration = $siteConfigurations->find();
	if ($siteConfigurations->error()) $page->addError($siteConfigurations->error());
	else $rows = $siteConfigurations->count();
