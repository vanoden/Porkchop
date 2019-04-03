<?php
    $page = new \Site\Page();
    $page->requireRole('package manager');

    if (isset($_REQUEST['package_code']) && strlen($_REQUEST['package_code'])) {
        $package = new \Package\Package();
        $package->get($_REQUEST['package_code']);
    }
    elseif (isset($_REQUEST['package_id']) && (is_numeric($_REQUEST['package_id']))) {
        $package = new \Package\Package($_REQUEST['package_id']);
    }
    else {
        $package_code = $GLOBALS['_REQUEST_']->query_vars_array[0];
        if (preg_match('/^[\w\-\.\_]+$/',$package_code)) {
            $package = new \Package\Package();
            if (! $package->get($package_code)) {
                if ($package->error) {
                    print "Error";
                    $page->addError($package->error);
                }
                else {
                    print "Not package!";
                    $page->addError("Package $package_code Not Found");
                }
            }
        }
    }

    if ($_REQUEST['btn_submit']) {
        $parameters = array();
        $parameters['name'] = $_REQUEST['name'];
        $parameters['description'] = $_REQUEST['description'];
        $parameters['license'] = $_REQUEST['license'];
        $parameters['owner_id'] = $_REQUEST['owner_id'];
        $parameters['status'] = $_REQUEST['status'];
        if (! $package->id) {
            $parameters['code'] = $_REQUEST['code'];
            $parameters['repository_id'] = $_REQUEST['repository_id'];
            $package = new \Package\Package();
            $package->add($parameters);
            if ($package->error) $page->addError($package->error);
        }
        elseif (! $package->update($parameters)) {
            $page->addError($package->error);
        }
        else {
            $page->success = 'Package updated';
        }
    }

    $role = new \Register\Role();
    $role->get('package manager');
    $admins = $role->members();

    $repositorylist = new \Storage\RepositoryList();
    $repositories = $repositorylist->find();
?>