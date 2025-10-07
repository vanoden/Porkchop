<?php
    $site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('configure site');

    if (!empty($_REQUEST['key']) && $siteConfiguration->validKey($_REQUEST['key'])) {
		$siteConfiguration = new \Site\Configuration($_REQUEST['key']);

        if (isset($_REQUEST['todo']) && !empty($_REQUEST['todo'])) {
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

	$siteConfigurations = new \Site\ConfigurationList();
	$configuration = $siteConfigurations->find();
	if ($siteConfigurations->error()) $page->addError($siteConfigurations->error());
	else $rows = $siteConfigurations->count();
