<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage terms of use');
	$can_proceed = true;

	// Initialize objects for validation
	$version = new \Site\TermsOfUseVersion();
	$tou = new \Site\TermsOfUse();
	
	// Validate version ID if provided
	$id = $_REQUEST['id'] ?? null;
	if (!empty($id)) {
		if (!$version->validInteger($id)) {
			$page->addError("Invalid version ID format");
			$can_proceed = false;
		} else {
			$version->load($id);
			if (!$version->id) {
				$page->addError("Version not found");
				$can_proceed = false;
			}
		}
	}

	// Validate ToU ID if provided
	$tou_id = $_REQUEST['tou_id'] ?? null;
	if (!empty($tou_id)) {
		if (!$tou->validInteger($tou_id)) {
			$page->addError("Invalid terms of use ID format");
			$can_proceed = false;
		} else {
			$tou = new \Site\TermsOfUse($tou_id);
			if (!$tou->id) {
				$page->addError("Terms of use not found");
				$can_proceed = false;
			}
		}
	} else if ($version->id) {
		// Get ToU from version if not directly specified
		$tou = $version->termsOfUse();
		if (!$tou->id) {
			$page->addError("Could not determine terms of use for this version");
			$can_proceed = false;
		}
	}

	// Handle form submission
	$btn_submit = $_REQUEST['btn_submit'] ?? null;
	if ($can_proceed && !empty($btn_submit)) {
		// Validate CSRF Token
		$csrfToken = $_POST['csrfToken'] ?? '';
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
			$page->addError("Invalid Request");
			$can_proceed = false;
		}
		
		// Check if we have a valid ToU
		if (empty($tou->id)) {
			$page->addError("No terms of use selected [tou_id=".$tou_id."]");
			$can_proceed = false;
		}
		
		// Validate content
		$content = $_REQUEST['content'] ?? '';
		if (empty($content)) {
			$page->addError("Content is required");
			$can_proceed = false;
		} elseif (!$version->validContent($content)) {
			$page->addError("Invalid content format");
			$can_proceed = false;
		}
		
		if ($can_proceed) {
			$page->appendSuccess("Form submitted");
			$parameters = array('content' => $content);
			
			if ($version->id) {
				$result = $version->update($parameters);
				if (!$result) {
					$page->addError("Failed to update version: " . $version->error());
				} else {
					$page->appendSuccess("Updated version " . $version->date_created());
				}
			} else {
				$version = $tou->addVersion($parameters);
				if ($tou->error()) {
					$page->addError($tou->error());
				} else if (!$version || !$version->id) {
					$page->addError("Failed to create new version");
				} else {
					$page->appendSuccess("Added Version " . $version->date_created());
				}
			}
		}
	}

	$page->title('Terms Of Use Version');
	$page->instructions = "Add some stuff here";
	$page->addBreadCrumb("Terms of Use","/_site/terms_of_use");
	
	if ($tou->id) {
		$page->addBreadCrumb($tou->name,"/_site/term_of_use?id=".$tou->id);
		$page->addBreadCrumb("Versions","/_site/tou_versions?tou_id=".$tou->id);
	}
	
	if ($version->id) {
		$page->addBreadCrumb($version->number());
		$version_number = $version->number();
	} else {
		$page->addBreadCrumb("New Version");
		$version->status = 'NEW';
		$version_number = "";
	}

	$tou_id = (!empty($tou->id)) ? $tou->id : 0;
	$tou_name = (!empty($tou->name)) ? $tou->name : "";
	$message_id = (!empty($message->id)) ? $message->id : 0;
