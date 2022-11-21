<?php
    $page = new \Site\Page();
    $page->requirePrivilege('manage packages');
    
    // @TODO for PHP7 warnings, fix object inheritance Package\Version::get, Storage\File::get    
    error_reporting(E_ERROR | E_PARSE);
    
    if (isset($_REQUEST['code'])) {
        $package = new \Package\Package();
        if (! $package->validCode($_REQUEST['code'])) {
            $page->addError("Invalid package code");
            $_REQUEST['code'] = null;
        }
        elseif (! $package->get($_REQUEST['code'])) $page->addError("Package not found");
    }

    if ($_REQUEST['dothis'] == 'publish') {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
            $page->addError("Invalid Request");
        }
        else {
            $version = new \Package\Version($_REQUEST['version_id']);
            app_log($GLOBALS['_SESSION_']->customer->login." publishing version ".$version->version()." of ".$version->package->name,'notice');
            $version->publish();
            if ($version->error) $page->addError($version->error);
        }
    }
    elseif ($_REQUEST['dothis'] == 'hide') {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
            $page->addError("Invalid Request");
        }
        else {
            $version = new \Package\Version($_REQUEST['version_id']);
            app_log("Hiding version ".$version->version()." of ".$version->package->name,'notice');
            $version->hide();
        }
    }
    elseif ($_REQUEST['dothis'] == 'download') {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
            $page->addError("Invalid Request");
        }
        else {
            $version = new \Package\Version($_REQUEST['version_id']);
		    $file = $version->file();
            $file->download();
            if ($file->error()) {
                $page->addError($file->error());
            }
            else {
                exit;
            }
        }
    }
    else {
        $_REQUEST['dothis'] = null;
    }

	$parameters = array();
	$parameters['package_id'] = $package->id;
	$parameters['_sort'] = 'version';
	$parameters['_sort_desc'] = true; 
    $versionList = new \Package\VersionList();
    $versions = $versionList->find($parameters);
