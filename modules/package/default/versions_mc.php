<?php
    $page = new \Site\Page();
    $page->requireRole('package manager');
    $page->requirePrivilege('manage engineering packages');
    
    // @TODO for PHP7 warnings, fix object inheritance Package\Version::get, Storage\File::get    
    error_reporting(E_ERROR | E_PARSE);

    if (isset($_REQUEST['csrfToken']) && !empty($_REQUEST['csrfToken']) && $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
    
        if (isset($_REQUEST['code'])) {
            $package = new \Package\Package();
            if (! $package->get($_REQUEST['code'])) $page->addError("Package not found");
        }

        if ($_REQUEST['dothis'] == 'publish') {
            $version = new \Package\Version($_REQUEST['version_id']);
            app_log($GLOBALS['_SESSION_']->customer->login." publishing version ".$version->version()." of ".$version->package->name,'notice');
            $version->publish();
            if ($version->error) $page->addError($version->error);
        }

        if ($_REQUEST['dothis'] == 'hide') {
            $version = new \Package\Version($_REQUEST['version_id']);
            app_log("Hiding version ".$version->version()." of ".$version->package->name,'notice');
            $version->hide();
        }

        if ($_REQUEST['dothis'] == 'download') {
            $version = new \Package\Version($_REQUEST['version_id']);
		    $file = $version->file();
            $file->download();
            if ($file->error()) {
                $page->addError($file->error());
            } else {
                exit;
            }
        }
    } else {
        $page->addError("Invalid Request");
    }
    
	$parameters = array();
	$parameters['package_id'] = $package->id;
	$parameters['_sort'] = 'version';
	$parameters['_sort_desc'] = true; 
    $versionList = new \Package\VersionList();
    $versions = $versionList->find($parameters);

