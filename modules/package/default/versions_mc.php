<?php
    $page = new \Site\Page();
    $page->requireRole('package manager');

    $versionList = new \Package\VersionList();
    $versions = $versionList->find(array('package_id' => $package->id));
?>