<?php
	$page = new \Site\Page();
    $page->requirePrivilege('manage packages');
	$packagelist = new \Package\PackageList();

	$parameters = array();
	if (isset($_REQUEST['status'])) {
        $package = new \Package\Package();
        if (! $package->validStatus($_REQUEST['status'])) {
            $page->addError("Invalid status");
            $_REQUEST['status'] = null;
        }
        else $parameters['status'] = $_REQUEST['status'];
    }
	if (isset($_REQUEST['repository_code'])) {
        $repository = new \Storage\Repository();
        if (! $repository->validCode($_REQUEST['repository_code'])) {
            $page->addError("Invalid Repository Code");
            $_REQUEST['repository_code'] = null;
        }
        elseif (! $repository->get($_REQUEST['repository_code'])) {
            $page->addError("Repository not found");
        }
        else $parameters['repository_code'] = $_REQUEST['repository_code'];
    }
	if (isset($_REQUEST['name'])) {
        if (! $package->validName($_REQUEST['name'])) {
            $page->addError("Invalid name");
            $_REQUEST['name'] = noXSS($_REQUEST['name']);
        }
        else $parameters['name'] = $_REQUEST['name'];
    }
    $_REQUEST['license'] = noXSS(trim($_REQUEST['license']));
    $_REQUEST['platform'] = noXSS(trim($_REQUEST['platform']));
	if (isset($_REQUEST['license'])) $parameters['license'] = $_REQUEST['license'];
	if (isset($_REQUEST['platform'])) $parameters['platform'] = $_REQUEST['platform'];
	$packages = $packagelist->find($parameters);