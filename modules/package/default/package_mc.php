<?php
    $page = new \Site\Page();
    $page->requirePrivilege('manage engineering packages');
    $can_proceed = true;

    // Create package object for validation
    $package = new \Package\Package();

    // Validate package identification
    if (!empty($_REQUEST['package_code'])) {
        if (!$package->validCode($_REQUEST['package_code'])) {
            $page->addError("Invalid package code format");
            $can_proceed = false;
        } else {
            if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
                $page->addError("Invalid Request");
                $can_proceed = false;
            }
            
            if ($can_proceed) {
                $package = new \Package\Package();
                $package->get($_REQUEST['package_code']);
                if (!$package->exists()) {
                    $page->addError("Package not found");
                    $can_proceed = false;
                }
            }
        }
    } elseif (!empty($_REQUEST['package_id'])) {
        if (!$package->validInteger($_REQUEST['package_id'])) {
            $page->addError("Invalid package ID format");
            $can_proceed = false;
        } else {
            if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
                $page->addError("Invalid Request");
                $can_proceed = false;
            }
            
            if ($can_proceed) {
                $package = new \Package\Package($_REQUEST['package_id']);
                if (!$package->exists()) {
                    $page->addError("Package not found");
                    $can_proceed = false;
                }
            }
        }
    } else {
        $package_code = $GLOBALS['_REQUEST_']->query_vars_array[0] ?? '';
        if (!$package->validCode($package_code)) {
            $page->addError("Invalid package code format");
            $can_proceed = false;
        } else {
            $package = new \Package\Package();
            if (!$package->get($package_code)) {
                if ($package->error) {
                    $page->addError($package->error);
                } else {
                    $page->addError("Package $package_code Not Found");
                }
                $can_proceed = false;
            }
        }
    }

    // Handle form submission
    if ($can_proceed && !empty($_REQUEST['btn_submit'])) {
        if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
            $page->addError("Invalid Request");
            $can_proceed = false;
        }
        
        if ($can_proceed) {
            // Validate owner
            if (!empty($_REQUEST['owner_id'])) {
                if (!$package->validInteger($_REQUEST['owner_id'])) {
                    $page->addError("Invalid owner ID format");
                    $can_proceed = false;
                } else {
                    $owner = new \Register\Person($_REQUEST['owner_id']);
                    if (!$owner->exists()) {
                        $_REQUEST['owner_id'] = $GLOBALS['_SESSION_']->customer->id;
                    }
                }
            } else {
                $_REQUEST['owner_id'] = $GLOBALS['_SESSION_']->customer->id;
            }
            
            // Validate status
            if (!$package->validStatus($_REQUEST['status'] ?? 'NEW')) {
                $page->addError("Invalid Status");
                $_REQUEST['status'] = 'NEW';
            }
            
            // Validate name
            if (empty($_REQUEST['name']) || !$package->validName($_REQUEST['name'])) {
                $page->addError("Invalid Name");
                $_REQUEST['name'] = null;
            }
            
            if ($can_proceed) {
                $parameters = array(
                    'name' => $_REQUEST['name'],
                    'description' => noXSS(trim($_REQUEST['description'] ?? '')),
                    'license' => noXSS(trim($_REQUEST['license'] ?? '')),
                    'platform' => noXSS(trim($_REQUEST['platform'] ?? '')),
                    'owner_id' => $_REQUEST['owner_id'],
                    'status' => $_REQUEST['status']
                );
                
                if (!$package->id) {
                    if (!$package->validCode($_REQUEST['code'] ?? '')) {
                        $page->addError("Invalid package code");
                        $can_proceed = false;
                    } else {
                        $parameters['code'] = $_REQUEST['code'];
                        $parameters['repository_id'] = $_REQUEST['repository_id'] ?? null;
                        $package = new \Package\Package();
                        $package->add($parameters);
                        if ($package->error) {
                            $page->addError($package->error);
                        } else {
                            $page->appendSuccess("Package added successfully");
                        }
                    }
                } else {
                    if (!$package->update($parameters)) {
                        $page->addError($package->error);
                    } else {
                        $page->appendSuccess("Package updated successfully");
                    }
                }
            }
        }
    }

    // Load data for display
    $role = new \Register\Role();
    $role->get('package manager');
    $admins = $role->members();

    $repositorylist = new \Storage\RepositoryList();
    $repositories = $repositorylist->find();

    $statii = $package->statii();
    
    $page->title("Package");
    $page->setAdminMenuSection("Package");  // Keep Package section open
    $page->addBreadcrumb("Package", "/_package/packages");