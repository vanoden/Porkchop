<?php
    $page = new \Site\Page();
    $page->requirePrivilege('manage packages');
    
    // @TODO for PHP7 warnings, fix object inheritance Package\Version::get, Storage\File::get    
    error_reporting(E_ERROR | E_PARSE);
    
    $can_proceed = true;
    
    if (isset($_REQUEST['code'])) {
        $package = new \Package\Package();
        if (! $package->validCode($_REQUEST['code'])) {
            $page->addError("Invalid package code");
            $_REQUEST['code'] = null;
            $can_proceed = false;
        }
        elseif (! $package->get($_REQUEST['code'])) {
            $page->addError("Package not found");
            $can_proceed = false;
        }
    }

    if ($can_proceed && isset($_REQUEST['dothis']) && $_REQUEST['dothis'] == 'publish') {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
            $page->addError("Invalid Request");
            $can_proceed = false;
        }
        else {
            $version = new \Package\Version();
            if ($version->validInteger($_REQUEST['version_id'] ?? null)) {
                $version = new \Package\Version($_REQUEST['version_id']);
                app_log($GLOBALS['_SESSION_']->customer->code." publishing version ".$version->version()." of ".$version->package->name,'notice');
                $version->publish();
                if ($version->error) $page->addError($version->error);
            } else {
                $page->addError("Invalid version ID");
            }
        }
    }
    elseif ($can_proceed && isset($_REQUEST['dothis']) && $_REQUEST['dothis'] == 'hide') {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
            $page->addError("Invalid Request");
            $can_proceed = false;
        }
        else {
            $version = new \Package\Version();
            if ($version->validInteger($_REQUEST['version_id'] ?? null)) {
                $version = new \Package\Version($_REQUEST['version_id']);
                app_log("Hiding version ".$version->version()." of ".$version->package->name,'notice');
                $version->hide();
            } else {
                $page->addError("Invalid version ID");
            }
        }
    }
    elseif ($can_proceed && isset($_REQUEST['dothis']) && $_REQUEST['dothis'] == 'download') {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
            $page->addError("Invalid Request");
            $can_proceed = false;
        }
        else {
            $version = new \Package\Version();
            if ($version->validInteger($_REQUEST['version_id'] ?? null)) {
                $version = new \Package\Version($_REQUEST['version_id']);
                $file = $version->file();
                $file->download();
                if ($file->error()) {
                    $page->addError($file->error());
                }
                else {
                    exit;
                }
            } else {
                $page->addError("Invalid version ID");
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
    
    $page->title("Versions");
    $page->setAdminMenuSection("Package");  // Keep Package section open
    $page->addBreadcrumb("Package", "/_package/packages");