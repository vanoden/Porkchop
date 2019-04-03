<?php
    $page = new \Site\Page();
    $page->requireRole('package manager');

    if (isset($_REQUEST['code'])) {
        $package = new \Package\Package();
        if (! $package->get($_REQUEST['code'])) {
            $page->addError("Package not found");
        }
    }

    $versionList = new \Package\VersionList();
    $versions = $versionList->find(array('package_id' => $package->id));

    if ($_REQUEST['dothis'] == 'publish') {
        $version = new \Package\Version($_REQUEST['version_id']);
        app_log("Publishing version ".$version->version()." of ".$version->package->name,'notice');
        $version->publish();
    }

    if ($_REQUEST['dothis'] == 'hide') {
        $version = new \Package\Version($_REQUEST['version_id']);
        app_log("Hiding version ".$version->version()." of ".$version->package->name,'notice');
        $version->hide();
    }
?>