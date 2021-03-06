<?php
    $page = new \Site\Page();
    $page->requireRole('package manager');
    
    // @TODO for PHP7 warnings, fix object inheritance Package\Version::get, Storage\File::get    
    error_reporting(E_ERROR | E_PARSE);

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
        $version->download();
        if ($version->error()) {
            $page->addError($version->error());
        } else {
            exit;
        }
    }
    
    $versionList = new \Package\VersionList();
    $versions = $versionList->find(array('package_id' => $package->id));

