<?php
    $page = new \Site\Page();
    $page->requirePrivilege('manage engineering packages');

    $package = new \Package\Package();
    if (isset($_REQUEST['package_code']) && $package->validCode($_REQUEST['package_code'])) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
        	$page->addError("Invalid Request");
        } else {
            $package = new \Package\Package();
            $package->get($_REQUEST['package_code']);
        }
    } elseif (isset($_REQUEST['package_id']) && (is_numeric($_REQUEST['package_id']))) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
        	$page->addError("Invalid Request");
        } else {
            $package = new \Package\Package($_REQUEST['package_id']);
        }
    } else {
        $package_code = $GLOBALS['_REQUEST_']->query_vars_array[0];
        if ($package->validCode($package_code)) {
            $package = new \Package\Package();
            if (! $package->get($package_code)) {
                if ($package->error) {
                    $page->addError($package->error);
                }
                else {
                    $page->addError("Package $package_code Not Found");
                }
            }
        }
    }
    
    if ($_REQUEST['btn_submit']) {
        if (! is_numeric($_REQUEST['owner_id'])) $_REQUEST['owner_id'] = $GLOBALS['_SESSION_']->customer->id;
        $owner = new \Register\Person($_REQUEST['owner_id']);
        if (! $owner->exists()) $_REQUEST['owner_id'] = $GLOBALS['_SESSION_']->customer->id;

        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
        	$page->addError("Invalid Request");
        }
        elseif(! $package->validStatus($_REQUEST['status'])) {
            $page->addError("Invalid Status");
            $_REQUEST['status'] = 'NEW';
        }
        elseif (! $package->validName($_REQUEST['name'])) {
            $page->addError("Invalid Name");
            $_REQUEST['name'] = null;
        }
        else {
            $parameters = array();
            $parameters['name'] = $_REQUEST['name'];
            $parameters['description'] = noXSS(trim($_REQUEST['description']));
            $parameters['license'] = noXSS(trim($_REQUEST['license']));
            $parameters['platform'] = noXSS(trim($_REQUEST['platform']));
            $parameters['owner_id'] = $_REQUEST['owner_id'];
            $parameters['status'] = $_REQUEST['status'];
            if (! $package->id) {
                $parameters['code'] = $_REQUEST['code'];
                $parameters['repository_id'] = $_REQUEST['repository_id'];
                $package = new \Package\Package();
                $package->add($parameters);
                if ($package->error) $page->addError($package->error);
            } elseif (! $package->update($parameters)) {
                $page->addError($package->error);
            } else {
                $page->success = 'Package updated';
            }
        }
    }

    $role = new \Register\Role();
    $role->get('package manager');
    $admins = $role->members();

    $repositorylist = new \Storage\RepositoryList();
    $repositories = $repositorylist->find();

	$statii = $package->statii();
