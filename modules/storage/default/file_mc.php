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
		}
	}
	
	if (preg_match('/^download$/i',$GLOBALS['_REQUEST_']->query_vars_array[1])) $_REQUEST['btn_submit'] = 'Download';
	
	if ($_REQUEST['btn_submit'] == 'Download') {
		$file->download();
	} elseif ($_REQUEST['btn_submit'] == 'Update') {
	
		if (! preg_match('/^\//',$_REQUEST['path'])) $_REQUEST['path'] = '/'.$_REQUEST['path'];
		$parameters = array(
			'display_name'	=> $_REQUEST['display_name'],
			'name'			=> $_REQUEST['name'],
			'path'			=> $_REQUEST['path']
		);
		$file->update($parameters);
		if ($file->error()) $page->addError("Update error: ".$file->error());
		else $page->success = "File updated";
		
	} elseif ($_REQUEST['btn_submit'] == 'Upload') {
	
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
