<?php
	// Initialize Page
	$site = new \Site();
	$page = $site->page();

	// Identify File from User Input
	if ($_REQUEST['id']) {
		$file = new \Storage\File($_REQUEST['id']);
	}
	else {
		$file = new \Storage\File();
		if ($_REQUEST['code']) {
			$file->get($_REQUEST['code']);
		}
		elseif ($GLOBALS['_REQUEST_']->query_vars_array[0]) {
			$file->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
		}
		else {
			// New File
		}
	}

	// Exit now if file is not readable!
	if (!empty($file->id) && ! $file->readable()) {
		$page->addError("Permission Denied");
		return 403;
	}

	// A file must be identitied before proceeding
	if (empty($file->id) && empty($_REQUEST['repository_id'])) $page->addError("No repository selected, return to <a href=\"/_storage/repositories\">/_storage/repositories</a>");

	if ($page->errorCount() < 1) {
		// File Download Requests
		if ((isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Download') || preg_match('/^download$/i',$GLOBALS['_REQUEST_']->query_vars_array[1])) {
			if ($file->readable()) {
				$file->download();
			}
			else {
				$page->addError("Permission Denied");
				return 403;
			}
		}
		// File Update Form Submitted
		elseif (isset($_REQUEST['btn_submit']) && !empty($_REQUEST['btn_submit'])) {
			if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
				$page->addError("Invalid Request");
			}
			else {
				if ($_REQUEST['btn_submit'] == 'Update') {
					if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
						$page->addError("Invalid Token");
					}
					else {
						if (! preg_match('/^\//',$_REQUEST['path'])) $_REQUEST['path'] = '/'.$_REQUEST['path'];
						$_REQUEST['display_name'] = htmlspecialchars($_REQUEST['display_name']);

						if (! $file->writePermitted()) {
							$page->addError("Permission Denied");
							return 403;
						}
						elseif (! $file->validPath($_REQUEST['path'])) {
							$page->addError("Invalid Path");
							$_REQUEST['path'] = htmlspecialchars($_REQUEST['path']);
						}
						elseif (!$file->validName($_REQUEST['name'])) {
							$page->addError("Invalid Name");
							$_REQUEST['name'] = htmlspecialchars($_REQUEST['name']);
						}
						else {
							$parameters = array(
								'display_name'	=> $_REQUEST['display_name'],
								'name'			=> $_REQUEST['name'],
								'path'			=> $_REQUEST['path']
							);
							$file->update($parameters);
							if ($file->error()) $page->addError("Update error: ".$file->error());
							else $page->success = "File updated";
						}
					}
				}
				elseif (isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Upload') {
					if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
						$page->addError("Invalid Token");
					}
					else {
						$page->requirePrivilege('upload storage files');
						if (! preg_match('/^\//',$_REQUEST['path'])) $_REQUEST['path'] = '/'.$_REQUEST['path'];
						$factory = new \Storage\RepositoryFactory();
						$repository = $factory->load($_REQUEST['repository_id']);

						if ($factory->error()) {
							$page->addError("Error loading repository: ".$factory->error());
						}
						else if (! $repository->id) {
							$page->addError("Repository not found");
						}
						else {
							app_log("Identified repo '".$repository->name."'");

							if ($uploadedFile = $repository->uploadFile($_FILES['uploadFile'],$_REQUEST['path'])) {
								$page->success = "File uploaded";
								$file = $uploadedFile;
							}
							else {
								$page->addError("Error uploading file: ".$repository->error());
							}
						}
					}
				}
				// Compile new privilege JSON
				$privilegeList = new \Resource\PrivilegeList($file->privilegeList());
				$privilegeList->apply($_REQUEST['privilege']);
				if ($_REQUEST['perm_level'] && $_REQUEST['perm_id']) {
					$privilegeList->grant($_REQUEST['perm_level'],$_REQUEST['perm_id'],$_REQUEST['perm_read'],$_REQUEST['perm_write']);
				}
				$privilegeJSON = $privilegeList->toJSON($_REQUEST['privilege']);
				$file->update(array('access_privileges' => $privilegeJSON));
			}
		}
	}

	if ($file->id) {
		$page->title = $file->name;
		$privileges = $file->privilegeList();
	}

	// Only those who can write to the repository can edit this file
	$repository = $file->repository();
	if (! $repository->writable($GLOBALS['_SESSION_']->customer->id)) {
		$page->addError("Permission Denied");
		return 403;
	}

	$page->addBreadcrumb("Storage");
	$page->addBreadcrumb("Repositories",'/_storage/repositories');
	$repository = $file->repository();
	if ($repository->id) {
		$page->addBreadcrumb($repository->name,'/_storage/repository/'.$repository->code);
		$page->addBreadcrumb($file->name);
	}
