<?php
// Initialize Page
$site = new \Site();
$page = $site->page();
$request = new \HTTP\Request();
$can_proceed = true;

// Capture S3-related app_log messages for display on page
$s3_log_messages = array();
$original_app_log = function_exists('app_log') ? 'app_log' : null;

// Override app_log function to capture S3 messages
if ($original_app_log) {
    function sapp_log($message, $level = 'debug', $path = null, $line = null) {
        global $s3_log_messages, $original_app_log;
        
        // Check if message is S3-related
        $s3_keywords = array('S3', 'AWS', 'bucket', 'upload', 's3', 'aws', 'spectros-test-site-images');
        $is_s3_related = false;
        
        foreach ($s3_keywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                $is_s3_related = true;
                break;
            }
        }
        
        // If S3-related, capture for display
        if ($is_s3_related) {
            $timestamp = date('H:i:s');
            $level_icon = '';
            switch ($level) {
                case 'error': $level_icon = '❌'; break;
                case 'warning': $level_icon = '⚠️'; break;
                case 'notice': $level_icon = 'ℹ️'; break;
                case 'info': $level_icon = 'ℹ️'; break;
                default: $level_icon = '🔍'; break;
            }
            $s3_log_messages[] = "[$timestamp] $level_icon [$level] $message";
        }
        
        // Call original app_log function
        return call_user_func($original_app_log, $message, $level, $path, $line);
    }
}

// Identify File from User Input
$file_id = $_REQUEST['id'] ?? null;
if ($request->validInteger($file_id) && $file_id > 0) {
	$file = new \Storage\File($file_id);
} else {
	$file = new \Storage\File();
	$file_code = $_REQUEST['code'] ?? null;
	if ($request->validText($file_code) && $file->validCode($file_code)) {
		$file->get($file_code);
	} elseif ($GLOBALS['_REQUEST_']->query_vars_array[0]) {
		$file->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	} else {
		// New File
	}
}

// Exit now if file is not readable!
if (!empty($file->id) && ! $file->readable()) {
	$page->addError("Permission Denied");
	return 403;
}

// A file must be identitied before proceeding
$repository_id = $_REQUEST['repository_id'] ?? null;
if (empty($file->id) && !$request->validInteger($repository_id)) {
	$page->addError("No repository selected, return to <a href=\"/_storage/repositories\">/_storage/repositories</a>");
	$can_proceed = false;
}

if ($can_proceed && $page->errorCount() < 1) {
	$btn_submit = $_REQUEST['btn_submit'] ?? null;
	$query_var = $GLOBALS['_REQUEST_']->query_vars_array[1] ?? null;
	
	// File Download Requests
	if (($request->validText($btn_submit) && $btn_submit == 'Download') || (!empty($query_var) && preg_match('/^download$/i', $query_var))) {
		if ($file->readable()) {
			$file->download();
		} else {
			$page->addError("Permission Denied");
			return 403;
		}
	}
	// File Update Form Submitted
	elseif ($request->validText($btn_submit)) {
		$csrfToken = $_POST['csrfToken'] ?? null;
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
			$page->addError("Invalid Request");
			$can_proceed = false;
		}
		else {
			if ($btn_submit == 'Update') {
				$csrfToken = $_REQUEST['csrfToken'] ?? null;
				if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
					$page->addError("Invalid Token");
					$can_proceed = false;
				}
				else {
					$path = $_REQUEST['path'] ?? '';
					$display_name = $_REQUEST['display_name'] ?? '';
					$name = $_REQUEST['name'] ?? '';

					if (!preg_match('/^\//', $path)) $path = '/' . $path;
						
					if (!$file->writePermitted()) {
						$page->addError("Permission Denied");
						return 403;
					}
					elseif (!$file->validPath($path)) {
						$page->addError("Invalid Path");
						$can_proceed = false;
					}
					elseif (!$file->validName($name)) {
						$page->addError("Invalid Name");
						$can_proceed = false;
					}
					else {
						$parameters = array(
							'display_name'	=> htmlspecialchars($display_name),
							'name'			=> $name,
							'path'			=> $path
						);
						$file->update($parameters);
						if ($file->error()) {
							$page->addError("Update error: " . $file->error());
							$can_proceed = false;
						}
						else $page->success = "File updated";
					}
				}
			}

			// File Upload Form Submitted
			elseif ($btn_submit == 'Upload' && $can_proceed) {
				$csrfToken = $_REQUEST['csrfToken'] ?? null;
				if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
					$page->addError("Invalid Token");
					$can_proceed = false;
				} else {
					$page->requirePrivilege('upload storage files');
					$path = $_REQUEST['path'] ?? '';

					if (!preg_match('/^\//', $path)) $path = '/' . $path;
					$repositoryBase = new \Storage\Repository($repository_id);
					$repository = $repositoryBase->getInstance();

					if ($repository->error()) {
						$page->addError("Error loading repository: " . $repository->error());
						$can_proceed = false;
					} else if (!$repository->id) {
						$page->addError("Repository not found");
						$can_proceed = false;
					} else if (!$repository->writable()) {
						$page->addError("Permission Denied");
						$can_proceed = false;
					} else {
						app_log("Identified repo '" . $repository->name . "'");

						if ($uploadedFile = $repository->uploadFile($_FILES['uploadFile'], $path)) {
							$page->success = "File uploaded";
							$file = $uploadedFile;
								
							// add metadata to the file if the keys and values are set
							$metadata_key = $_REQUEST['storage_file_metadata_key'] ?? null;
							$metadata_value = $_REQUEST['storage_file_metadata_value'] ?? null;
								
							if ($request->validText($metadata_key) && $request->validText($metadata_value)) {
								// if the file is a product image, add it to the product
								if ($metadata_key == 'spectros_product_image') {
									$product = new \Product\Item($metadata_value);
									$product->addImage($file->id);
								}
							}
						} else {
							$page->addError("Error uploading file: " . $repository->error());
							$can_proceed = false;
						}
						
						// Display captured S3 log messages as page errors
						if (!empty($s3_log_messages)) {
							$page->addError("S3 Debug Information:");
							foreach ($s3_log_messages as $log_message) {
								$page->addError($log_message);
							}
						}
					}
				}
			}
			
			// Compile new privilege JSON
			if ($can_proceed) {
				$privilege = $_REQUEST['privilege'] ?? array();
				$privilegeList = new \Resource\PrivilegeList($file->privilegeList());
				$privilegeList->apply($privilege);
				
				$perm_level = $_REQUEST['perm_level'] ?? null;
				$perm_id = $_REQUEST['perm_id'] ?? null;
				
				if ($request->validText($perm_level) && $request->validText($perm_id)) {
					$perm_read = $_REQUEST['perm_read'] ?? 0;
					if (!$request->validInteger($perm_read)) $perm_read = 0;
					$perm_write = $_REQUEST['perm_write'] ?? 0;
					if (!$request->validInteger($perm_write)) $perm_write = 0;
					$privilegeList->grant($perm_level, $perm_id, $perm_read, $perm_write);
				}
				
				$privilegeJSON = $privilegeList->toJSON($privilege);
				$file->update(array('access_privileges' => $privilegeJSON));
			}
		}
	}
}

if ($file->id) {
	$page->title = $file->name;
	$privileges = $file->privilegeList();
}

// Only those who can write to the repository can edit this file
$repository = $file->repository();
if (empty($repository) || !$repository->writable($GLOBALS['_SESSION_']->customer->id)) {
	$page->addError("Permission Denied");
	return 403;
}

$page->addBreadcrumb("Storage");
$page->addBreadcrumb("Repositories", '/_storage/repositories');
$repository = $file->repository();
if ($repository->id) {
	$page->addBreadcrumb($repository->name, '/_storage/repository/' . $repository->code);
	$page->addBreadcrumb($file->name);
}
