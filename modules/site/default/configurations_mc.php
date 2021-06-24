<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('administrator');
	
    $siteConfiguration = new \Site\Configuration();
    if (!empty($_REQUEST['key'])) $siteConfiguration = new \Site\Configuration($_REQUEST['key']);
    if (isset($_REQUEST['todo'])) {
        switch ($_REQUEST['todo']) {
            case 'drop':
                $siteConfiguration->delete();
                break;

            case 'add':
                $siteConfiguration->key = $_REQUEST['key'];
                $siteConfiguration->set($_REQUEST['value']);
                break;
                
            case 'update':
                $siteConfiguration->set($_REQUEST['value']);
                break;
        }
    }
    
	$siteConfigurations = new \Site\ConfigurationList();
	$configuration = $siteConfigurations->find();
