<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requirePrivilege('configure site');
	
    $siteConfiguration = new \Site\Configuration();
    if (!empty($_REQUEST['key'])) $siteConfiguration = new \Site\Configuration($_REQUEST['key']);
    if (isset($_REQUEST['todo']) && !empty($_REQUEST['todo'])) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
            $page->addError("Invalid Request");
        } else {
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
    
	$siteConfigurations = new \Site\ConfigurationList();
	$configuration = $siteConfigurations->find();
