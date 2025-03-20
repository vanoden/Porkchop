<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage terms of use');
	$can_proceed = true;

	// Initialize terms of use object for validation
	$tou = new \Site\TermsOfUse();
	
	// Validate ID if provided
	$id = $_REQUEST['id'] ?? null;
	if (!empty($id)) {
		if (!$tou->validInteger($id)) {
			$page->addError("Invalid terms of use ID format");
			$can_proceed = false;
		} else {
			$tou = new \Site\TermsOfUse($id);
			if (!$tou->exists()) {
				$page->addError("Requested Terms of Use Agreement not found");
				http_response_code(404);
				$can_proceed = false;
			}
		}
	}

	// Handle form submission
	$btn_submit = $_REQUEST['btn_submit'] ?? null;
	if ($can_proceed && !empty($btn_submit)) {
		// Validate CSRF Token
		$csrfToken = $_REQUEST['csrfToken'] ?? '';
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
			$page->addError("Invalid Token");
			$can_proceed = false;
		}
		
		// Validate form fields
		$name = $_REQUEST['name'] ?? '';
		$description = $_REQUEST['description'] ?? '';
		
		if (empty($name)) {
			$page->addError("Name is required");
			$can_proceed = false;
		} elseif (!$tou->validText($name)) {
			$page->addError("Invalid name format");
			$can_proceed = false;
		}
		
		if (empty($description)) {
			$page->addError("Description is required");
			$can_proceed = false;
		} elseif (!$tou->validText($description)) {
			$page->addError("Invalid description format");
			$can_proceed = false;
		}
		
		if ($can_proceed) {
			$parameters = array(
				'name' => $name,
				'description' => $description
			);
			
			if ($tou->id) {
				if (!$tou->update($parameters)) {
					$page->addError("Could not update Terms of Use: ".$tou->error());
				} else {
					$page->appendSuccess("Terms of Use record updated");
				}
			} else {
				if (!$tou->add($parameters)) {
					$page->addError("Could not add Terms of Use: ".$tou->error());
				} else {
					$page->appendSuccess("Terms of Use record created");
				}
			}
		}
	}

	if ($tou->id) $page->instructions = "Update values and click Submit to update this Terms of Use record";
	else $page->instructions = "Fill out form and click Submit to create a new Terms Of Use record";
	$page->addBreadCrumb("Terms of Use","/_site/terms_of_use");
	if ($tou->id) $page->addBreadCrumb($tou->name);
	else $page->addBreadCrumb("New Terms of Use");
