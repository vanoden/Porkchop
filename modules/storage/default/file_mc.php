<?php
	$site = new \Site();
	$page = $site->page();

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

	if (!empty($file->id) && ! $file->readable()) {
		$page->addError("Permission Denied");
		return 403;
	}

	if (empty($file->id) && empty($_REQUEST['repository_id'])) $page->addError("No repository selected, return to <a href=\"/_storage/repositories\">/_storage/repositories</a>");

	if ($page->errorCount() < 1) {
		if ((isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Download') || preg_match('/^download$/i',$GLOBALS['_REQUEST_']->query_vars_array[1])) {
			if ($file->readPermitted()) {
				$file->download();
			}
			else {
				$page->addError("Permission Denied");
				return 403;
			}
		}
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

							// Check for Errors
							if ($_FILES['uploadFile']['error'] == 1) {
								$page->addError("Uploaded file too large");
								app_log("Upload file exceeds the upload_max_filesize directive",'info');
							}
							elseif ($_FILES['uploadFile']['error'] == 2) {
								$page->addError("Uploaded file too large");
								app_log("Uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form",'info');
							}
							elseif ($_FILES['uploadFile']['error'] == 3) {
								$page->addError("Upload failed before completion");
								app_log("The uploaded file was only partially uploaded",'info');
							}
							elseif ($_FILES['uploadFile']['error'] == 4) {
								$page->addError("No file was uploaded");
								app_log("No file was uploaded",'info');
							}
							elseif ($_FILES['uploadFile']['error'] == 6) {
								$page->addError("Server error uploading file");
								app_log("Upload failed: Temporary folder unavailable",'error');
							}
							elseif ($_FILES['uploadFile']['error'] == 7) {
								$page->addError("Server error uploading file");
								app_log("Upload failed: Failed to write upload to disk",'error');
							}
							elseif ($_FILES['uploadFile']['error'] == 8) {
								$page->addError("Server error uploading file");
								app_log("Upload failed: Upload blocked by extension",'error');
							}
							elseif (! file_exists($_FILES['uploadFile']['tmp_name'])) {
								$page->addError("Temp file '".$_FILES['uploadFile']['tmp_name']."' not found");
							}
							else {
							
								// Check for Conflict 
								$filelist = new \Storage\FileList();
								list($existing) = $filelist->find(
									array(
										'repository_id' => $repository->id,
										'path'	=> $_REQUEST['path'],
										'name' => $_FILES['uploadFile']['name'],
									)
								);
								
								if ($existing->id) {
									$page->addError("File already exists with that name in repo ".$repository->name);
								} else {
								
									// Add File to Library 
									$file = new \Storage\File();
									if ($file->error()) error("Error initializing file: ".$file->error());
									$file->add(
										array(
											'repository_id'     => $repository->id,
											'name'              => $_FILES['uploadFile']['name'],
											'path'				=> $_REQUEST['path'],
											'mime_type'         => $_FILES['uploadFile']['type'],
											'size'              => $_FILES['uploadFile']['size'],
										)
									);

									// Upload File Into Repository 
									if ($file->error()) $page->addError("Error adding file: ".$file->error());
									elseif (! $repository->addFile($file,$_FILES['uploadFile']['tmp_name'])) {
										$file->delete();
										$page->addError('Unable to add file to repository: '.$repository->error());
									} else {
										app_log("Stored file ".$file->id." at ".$repostory->path."/".$file->code);
										$page->success = "File uploaded";
									}
								}
							}
						}
					}
				}

				// Create a complete privilege map
				$privileges = $file->privilegeList();
				$keys = [];
				$keys['a']['0'] = 1;
				foreach ($privileges as $level => $levelData) {
					foreach ($levelData as $id => $privilege) {
						if (! $id) continue;
						$keys[$level][$id] = 1;
					}
				}

				foreach ($_REQUEST['privilege'] as $level => $levelData) {
					foreach ($levelData as $entityId => $privilege) {
						$level = preg_replace('/\'/','',$level);
						$entityId = preg_replace('/\'/','',$entityId);
						$keys[$level][$entityId] = 1;
					}
				}

				foreach ($keys as $level => $sublevel) {
					foreach ($sublevel as $id => $y) {
						foreach (array('read','write') as $action) {
							$qlevel = "'$level'";
							$qaction = "'".substr($action,0,1)."'";
							$postKey = "'$level',$id,'".substr($action,0,1)."'";

							$existing = $privileges[$level][$id]->$action;
							$form = $_REQUEST['privilege'][$qlevel][$id][$qaction];
							if ($existing && ! $form) {
								$page->success .= "<br>\nRevoked ".$level." ".$id." ".$action."";
								if (!$file->revoke($level,$id,$action)) $page->addError("Error revoking privilege: ".$file->error());
							}
							elseif (! $existing && $form) {
								$page->success .= "<br>\nGranted ".$level." ".$id." ".$action."";
								if (!$file->grant($level,$id,$action)) $page->addError("Error granting privilege: ".$file->error());
							}
						}
					}
				}

				if(!empty($_REQUEST['perm_level']) && !empty($_REQUEST['perm_id'])) {
					if ($_REQUEST['perm_level'] == 'u') {
						$entity = new \Register\Customer($_REQUEST['perm_id']);
						if (! $entity->id) $page->addError("User ".$_REQUEST['perm_id']." not found");
					}
					elseif ($_REQUEST['perm_level'] == 'r') {
						$entity = new \Register\Role($_REQUEST['perm_id']);
						if (! $entity->id) $page->addError("Role ".$_REQUEST['perm_id']." not found");
					}
					elseif ($_REQUEST['perm_level'] == 'o') {
						$entity = new \Register\Organization($_REQUEST['perm_id']);
						if (! $entity->id) $page->addError("Organization ".$_REQUEST['perm_id']." not found");
					}

					if ($entity->id > 0) {
						if ($_REQUEST['perm_read'] == 1) {
							if ($file->grant($_REQUEST['perm_level'],$_REQUEST['perm_id'],'r'))
							$page->success .= "<br>\nGranted ".$perm_level." ".$perm_id." read";
							else $page->addError($file->error());
						}
						if ($_REQUEST['perm_write'] == 1) {
							if ($file->grant($_REQUEST['perm_level'],$_REQUEST['perm_id'],'w'))
							$page->success .= "<br>\nGranted ".$perm_level." ".$perm_id." write";
							else $page->addError($file->error());
						}
					}
				}
			}
		}
	}

	if ($file->id) {
		$page->title = $file->name;
		$privileges = $file->privilegeList();
	}
	$page->addBreadcrumb("Storage");
	$page->addBreadcrumb("Repositories",'/_storage/repositories');
	$repository = $file->repository();
	if ($repository->id) {
		$page->addBreadcrumb($repository->name,'/_storage/repository/'.$repository->code);
		$page->addBreadcrumb($file->name);
	}
