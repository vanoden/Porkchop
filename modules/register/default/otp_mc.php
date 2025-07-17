<?php
$page = new \Site\Page();
$can_proceed = true;

	// DEBUG: Log OTP page access
	app_log("=== OTP PAGE ACCESS ===", 'debug', __FILE__, __LINE__, 'otplogs');
	app_log("Request method: " . $_SERVER['REQUEST_METHOD'], 'debug', __FILE__, __LINE__, 'otplogs');
	app_log("Session ID: " . $GLOBALS['_SESSION_']->id, 'debug', __FILE__, __LINE__, 'otplogs');
	app_log("Customer ID: " . ($GLOBALS['_SESSION_']->customer->id ?? 'null'), 'debug', __FILE__, __LINE__, 'otplogs');
	app_log("Current OTP verified status: " . ($GLOBALS['_SESSION_']->isOTPVerified() ? 'true' : 'false'), 'debug', __FILE__, __LINE__, 'otplogs');
	app_log("Server time: " . date('Y-m-d H:i:s'), 'debug', __FILE__, __LINE__, 'otplogs');
	app_log("Server timestamp: " . time(), 'debug', __FILE__, __LINE__, 'otplogs');
	app_log("POST data: " . json_encode($_POST), 'debug', __FILE__, __LINE__, 'otplogs');
	app_log("REQUEST data: " . json_encode($_REQUEST), 'debug', __FILE__, __LINE__, 'otplogs');
	app_log("Raw POST input: " . file_get_contents('php://input'), 'debug', __FILE__, __LINE__, 'otplogs');
	app_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'), 'debug', __FILE__, __LINE__, 'otplogs');

// Check for recovery token first
$recovery_token = $_REQUEST['recovery_token'] ?? null;
if (!empty($recovery_token)) {
	$customer = new \Register\Customer();
	if ($customer->verifyOTPRecoveryToken($recovery_token)) {
		// Valid recovery token - redirect to reset_otp page
		header("Location: /_register/reset_otp");
		exit;
	}
	else {
		$page->addError("Invalid or expired recovery token");
		$can_proceed = false;
	}
}
else {
	// Normal flow - check if user is logged in
	if (!$GLOBALS['_SESSION_']->customer->id) {
		$page->addError("Please log in first");
		header("Location: /_register/login");
		exit;
	}
}

if ($can_proceed) {
	// DEBUG: Log customer and secret key info
	app_log("=== CUSTOMER AND SECRET KEY INFO ===", 'debug', __FILE__, __LINE__, 'otplogs');
	
	// get the secret key from the database
	$customerId = $GLOBALS['_SESSION_']->customer->id;
	app_log("Customer ID: " . $customerId, 'debug', __FILE__, __LINE__, 'otplogs');
	
	$customer = new \Register\Customer($customerId);
	app_log("Customer object created, error: " . ($customer->error() ?: 'none'), 'debug', __FILE__, __LINE__, 'otplogs');
	
	$userStoredSecret = $customer->otp_secret_key();
	app_log("User stored secret length: " . strlen($userStoredSecret), 'debug', __FILE__, __LINE__, 'otplogs');
	app_log("User stored secret (first 10 chars): " . substr($userStoredSecret, 0, 10) . "...", 'debug', __FILE__, __LINE__, 'otplogs');
  
	// Show QR code if no secret 2FA key is found
	$showQRCode = false;
	if (empty($userStoredSecret)) {
		// DEBUG: Creating new TOTP instance
		app_log("=== CREATING NEW TOTP INSTANCE ===", 'debug', __FILE__, __LINE__, 'otplogs');
		app_log("Customer code: " . $customer->code, 'debug', __FILE__, __LINE__, 'otplogs');
		app_log("Site hostname: " . $GLOBALS['_config']->site->hostname, 'debug', __FILE__, __LINE__, 'otplogs');
		
		// Create new TOTP instance with new secret
		$tfa = new \Register\AuthenticationService\TwoFactorAuth(null, $customer->code, $GLOBALS['_config']->site->hostname);
		app_log("TOTP instance created successfully", 'debug', __FILE__, __LINE__, 'otplogs');
		
		$userStoredSecret = $tfa->getSecret();
		app_log("New secret generated, length: " . strlen($userStoredSecret), 'debug', __FILE__, __LINE__, 'otplogs');
		
		$updateResult = $customer->update(array('secret_key' => $userStoredSecret));
		app_log("Customer update result: " . ($updateResult ? 'success' : 'failed'), 'debug', __FILE__, __LINE__, 'otplogs');
		if (!$updateResult) {
			app_log("Customer update error: " . $customer->error(), 'debug', __FILE__, __LINE__, 'otplogs');
		}
		
		$showQRCode = true;
		app_log("Show QR code: true", 'debug', __FILE__, __LINE__, 'otplogs');
	}
	else {
		// DEBUG: Using existing TOTP instance
		app_log("=== USING EXISTING TOTP INSTANCE ===", 'debug', __FILE__, __LINE__, 'otplogs');
		app_log("Customer code: " . $customer->code, 'debug', __FILE__, __LINE__, 'otplogs');
		app_log("Site hostname: " . $GLOBALS['_config']->site->hostname, 'debug', __FILE__, __LINE__, 'otplogs');
		
		// Create TOTP instance with existing secret
		$tfa = new \Register\AuthenticationService\TwoFactorAuth($userStoredSecret, $customer->code, $GLOBALS['_config']->site->hostname);
		app_log("TOTP instance created successfully", 'debug', __FILE__, __LINE__, 'otplogs');
		
		$showQRCode = false;
		app_log("Show QR code: false", 'debug', __FILE__, __LINE__, 'otplogs');
	}

	// Verification
	$isVerified = false;
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// DEBUG: Log verification process
		app_log("=== OTP VERIFICATION PROCESS ===", 'debug', __FILE__, __LINE__, 'otplogs');
		
		$userSubmittedCode = $_POST['verification_code'] ?? null;
		app_log("User submitted code: " . ($userSubmittedCode ?: 'null'), 'debug', __FILE__, __LINE__, 'otplogs');
		
		// Validate the verification code
		if (empty($userSubmittedCode)) {
			app_log("ERROR: No code submitted", 'debug', __FILE__, __LINE__, 'otplogs');
			$page->addError("No code submitted.");
			$can_proceed = false;
		}
		elseif (!preg_match('/^[0-9]{6}$/', $userSubmittedCode)) {
			app_log("ERROR: Invalid code format - must be 6 digits, got: " . $userSubmittedCode, 'debug', __FILE__, __LINE__, 'otplogs');
			$page->addError("Invalid code format. Must be 6 digits.");
			$can_proceed = false;
	    }
		else {
			// DEBUG: Detailed verification attempt logging
			app_log("=== DETAILED VERIFICATION ATTEMPT ===", 'debug', __FILE__, __LINE__, 'otplogs');
			app_log("User submitted code: " . $userSubmittedCode, 'debug', __FILE__, __LINE__, 'otplogs');
			app_log("Secret key length: " . strlen($userStoredSecret), 'debug', __FILE__, __LINE__, 'otplogs');
			app_log("Secret key (first 10 chars): " . substr($userStoredSecret, 0, 10) . "...", 'debug', __FILE__, __LINE__, 'otplogs');
			
			// Get current expected code for debugging
			$currentCode = $tfa->getCurrentCode();
			app_log("Current expected code: " . $currentCode, 'debug', __FILE__, __LINE__, 'otplogs');
			
			// Get previous and next codes for debugging
			$previousCode = $tfa->getCode(time() - 30);
			$nextCode = $tfa->getCode(time() + 30);
			app_log("Previous code (30s ago): " . $previousCode, 'debug', __FILE__, __LINE__, 'otplogs');
			app_log("Next code (30s from now): " . $nextCode, 'debug', __FILE__, __LINE__, 'otplogs');
			
			// Check if submitted code matches any of the expected codes
			$codeMatches = array();
			if ($userSubmittedCode === $currentCode) $codeMatches[] = 'current';
			if ($userSubmittedCode === $previousCode) $codeMatches[] = 'previous';
			if ($userSubmittedCode === $nextCode) $codeMatches[] = 'next';
			app_log("Code matches: " . implode(', ', $codeMatches), 'debug', __FILE__, __LINE__, 'otplogs');
			
			// Try verification with different windows
			$verifyResult = $tfa->verifyCode($userSubmittedCode);
			app_log("TOTP verifyCode() result: " . ($verifyResult ? 'true' : 'false'), 'debug', __FILE__, __LINE__, 'otplogs');
			
			if ($verifyResult) {
				app_log("=== OTP VERIFICATION SUCCESSFUL ===", 'debug', __FILE__, __LINE__, 'otplogs');
				$page->appendSuccess("Verification successful, please wait...");
				$isVerified = true;

				// Update session state and cache
				app_log("Setting OTP verified to true", 'debug', __FILE__, __LINE__, 'otplogs');
				$setResult = $GLOBALS['_SESSION_']->setOTPVerified(true);
				app_log("setOTPVerified result: " . ($setResult ? 'true' : 'false'), 'debug', __FILE__, __LINE__, 'otplogs');

				// Clear the refer_url to allow access to the target page
				app_log("Clearing refer_url", 'debug', __FILE__, __LINE__, 'otplogs');
				$updateResult = $GLOBALS['_SESSION_']->update(array('refer_url' => null));
				app_log("Update refer_url result: " . ($updateResult ? 'true' : 'false'), 'debug', __FILE__, __LINE__, 'otplogs');

				// Audit successful OTP verification
				app_log("Recording audit", 'debug', __FILE__, __LINE__, 'otplogs');
				$auditResult = $customer->auditRecord('OTP_VERIFIED', 'OTP code verified successfully');
				app_log("Audit result: " . ($auditResult ? 'true' : 'false'), 'debug', __FILE__, __LINE__, 'otplogs');
				
				// Redirect to target or default page
				$target = $GLOBALS['_SESSION_']->refer_url ?? '/_register/account';
				app_log("Redirecting to: " . $target, 'debug', __FILE__, __LINE__, 'otplogs');
				header("Location: " . $target);
				exit;
			}
			else {
				app_log("=== OTP VERIFICATION FAILED ===", 'debug', __FILE__, __LINE__, 'otplogs');
				app_log("Submitted code: " . $userSubmittedCode, 'debug', __FILE__, __LINE__, 'otplogs');
				app_log("Expected codes - Current: " . $currentCode . ", Previous: " . $previousCode . ", Next: " . $nextCode, 'debug', __FILE__, __LINE__, 'otplogs');
				app_log("TOTP verifyCode() returned false", 'debug', __FILE__, __LINE__, 'otplogs');
				$page->addError("Invalid code");
				$can_proceed = false;
			}
		}
  	}

	// Generate QR code as data URI for img tag (always generate if needed for template)
	$qrCodeData = $tfa->getQRCodeImage();

	// Save the target URL in the session if a new one is provided
	$target = $_REQUEST['target'] ?? null;
	if (isset($target) && !empty($target)) {
		// Validate the target URL to prevent potential security issues
		// Allow query parameters and common URL-safe characters
		if (!filter_var($target, FILTER_VALIDATE_URL) && !preg_match('/^\/[a-zA-Z0-9\/_\-]*(\?[a-zA-Z0-9\/_\-=&]*)?$/', $target)) {
			$page->addError("Invalid target URL format");
		}
		else {
			$GLOBALS['_SESSION_']->update(array('refer_url' => $target));
		}
	}
}
