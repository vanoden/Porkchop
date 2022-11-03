<?php
	$page = new \Site\Page();
	if ($_REQUEST['id']) {
		$file = new \Storage\File($_REQUEST['id']);
	} else {
		$file = new \Storage\File();
		if ($_REQUEST['code']) {
			$file->get($_REQUEST['code']);
		} elseif ($GLOBALS['_REQUEST_']->query_vars_array[0]) {
			$file->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
		} else {
			// New File
		}
	}

	if (empty($file->id) && empty($_REQUEST['repository_id'])) $page->addError("No repository selected, return to <a href=\"/_storage/repositories\">/_storage/repositories</a>");
	
	if ($page->errorCount() < 1) {
	    if (isset($_REQUEST['btn_submit']) && !empty($_REQUEST['btn_submit'])) {
            if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
                $page->addError("Invalid Request");
            } else {
                if (preg_match('/^download$/i',$GLOBALS['_REQUEST_']->query_vars_array[1])) $_REQUEST['btn_submit'] = 'Download';
		        
		        if (isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Download') {
			        $file->download();
		        } elseif ($_REQUEST['btn_submit'] == 'Update') {
			        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
				        $page->addError("Invalid Token");
			        }
			        else {
				        if (! preg_match('/^\//',$_REQUEST['path'])) $_REQUEST['path'] = '/'.$_REQUEST['path'];
				        if (! $file->validPath($_REQUEST['path'])) {
					        $page->addError("Invalid Path");
					        $_REQUEST['path'] = htmlspecialchars($_REQUEST['path']);
				        }
				        elseif (!$file->validName($_REQUEST['name'])) {
					        $page->addError("Invalid Name");
					        $_REQUEST['name'] = htmlspecialchars($_REQUEST['name']);
				        }
				        $_REQUEST['display_name'] = htmlspecialchars($_REQUEST['display_name']);
				        $parameters = array(
					        'display_name'	=> $_REQUEST['display_name'],
					        'name'			=> $_REQUEST['name'],
					        'path'			=> $_REQUEST['path']
				        );
				        $file->update($parameters);
				        if ($file->error()) $page->addError("Update error: ".$file->error());
				        else $page->success = "File updated";
			        }
		        } elseif (isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Upload') {
			        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
				        $page->addError("Invalid Token");
			        }
			        else {
				        $page->requirePrivilege('upload storage files');
				        if (! preg_match('/^\//',$_REQUEST['path'])) $_REQUEST['path'] = '/'.$_REQUEST['path'];
				        $factory = new \Storage\RepositoryFactory();
				        $repository = $factory->load($_REQUEST['repository_id']);
				        
				        if ($factory->error) {
					        $page->addError("Error loading repository: ".$factory->error);
				        } else if (! $repository->id) {
					        $page->addError("Repository not found");
				        } else {
					        app_log("Identified repo '".$repository->name."'");
					        
					        if (! file_exists($_FILES['uploadFile']['tmp_name'])) {
						        $page->addError("Temp file '".$_FILES['uploadFile']['tmp_name']."' not found");
					        } else {
					        
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
							        if ($file->error) error("Error initializing file: ".$file->error);
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
							        if ($file->error) $page->addError("Error adding file: ".$file->error);
							        elseif (! $repository->addFile($file,$_FILES['uploadFile']['tmp_name'])) {
								        $file->delete();
								        $page->addError('Unable to add file to repository: '.$repository->error);
							        } else {
								        app_log("Stored file ".$file->id." at ".$repostory->path."/".$file->code);
								        $page->success = "File uploaded";
							        }
						        }
					        }
				        }
			        }
		        }
            }
	    }
	}
