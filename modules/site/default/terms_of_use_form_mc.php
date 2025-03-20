<?php
	$site = new \Site();
	$page = $site->page();
	$page->requireAuth();
	$can_proceed = true;

	// Initialize for validation
	$tou = new \Site\TermsOfUse();
	
	// Validate target page parameters
	$module = $_REQUEST['module'] ?? '';
	$view = $_REQUEST['view'] ?? '';
	$index = $_REQUEST['index'] ?? '';
	
	if (empty($module) || empty($view)) {
		$page->addError("Missing required target page parameters");
		$can_proceed = false;
	} elseif (!$tou->validText($module) || !$tou->validText($view)) {
		$page->addError("Invalid target page parameters");
		$can_proceed = false;
	}
	
	if (empty($index)) {
		$page->addError("Missing required target page index parameter");
		$can_proceed = false;
	} elseif (!$tou->validInteger($index)) {
		$page->addError("Invalid target page index parameter");
		$can_proceed = false;
	}

	$target_page = new \Site\Page();
	if ($can_proceed && !$target_page->get($module, $view, $index)) {
		$page->addError("Target Page Not Found");
		$can_proceed = false;
	} else if ($can_proceed) {
		$tou = $target_page->tou();
		$latest_version = $tou->latestVersion();
		
		if ($tou->error()) {
			$page->addError($tou->error());
			$can_proceed = false;
		} elseif (!$latest_version) {
			$page->addError('No published version of ToU: ' . $tou->name);
			$can_proceed = false;
		} else if ($GLOBALS['_SESSION_']->customer->acceptedTOU($tou->id)) {
			// User has already accepted this ToU, redirect to target page
			header("Location: /_" . $target_page->module() . "/" . $target_page->view() . "/" . $target_page->index());
			exit;
		}
	}

	// Handle form submission
	$btn_submit = $_REQUEST['btn_submit'] ?? '';
	if ($can_proceed && !empty($btn_submit)) {
		// Validate CSRF Token
		$csrfToken = $_REQUEST['csrfToken'] ?? '';
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
			$page->addError("Invalid Token");
			$can_proceed = false;
		}
		
		// Validate version_id
		$version_id = $_REQUEST['version_id'] ?? null;
		if (empty($version_id)) {
			$page->addError("Version ID is required");
			$can_proceed = false;
		} elseif (!$tou->validInteger($version_id)) {
			$page->addError("Invalid version ID format");
			$can_proceed = false;
		}
		
		if ($can_proceed) {
			if ($btn_submit == 'Accept') {
				if ($GLOBALS['_SESSION_']->customer->acceptTOU($version_id)) {
					header("Location: " . $target_page->uri());
					exit;
				} else {
					$page->addError($GLOBALS['_SESSION_']->customer->error());
				}
			} else {
				if ($GLOBALS['_SESSION_']->customer->declineTOU($version_id)) {
					header("Location: /_site/terms_of_use_declined?module=" . $target_page->module() . "&view=" . $target_page->view() . "&index=" . $target_page->index());
					exit;
				} else {
					$page->addError($GLOBALS['_SESSION_']->customer->error());
				}
			}
		}
	}