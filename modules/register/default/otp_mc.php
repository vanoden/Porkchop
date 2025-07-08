<?php
$page = new \Site\Page();
$can_proceed = true;

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
	// get the secret key from the database
	$customerId = $GLOBALS['_SESSION_']->customer->id;
	$customer = new \Register\Customer($customerId);
	$tfa = new \Register\AuthenticationService\TwoFactorAuth(null, $customer->code, $GLOBALS['_config']->site->hostname);
	$userStoredSecret = $customer->secret_key();
  
	// Show QR code if no secret 2FA key is found
	$showQRCode = false;
	if (empty($userStoredSecret)) {
		$secret = $tfa->getSecret();
		$userStoredSecret = $secret;
		$customer->update(array('secret_key' => $userStoredSecret));
		$showQRCode = true;
	}
	else {
		$showQRCode = false;
	}

	// Verification
	$isVerified = false;
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$userSubmittedCode = $_POST['verification_code'] ?? null;
		// Validate the verification code
		if (empty($userSubmittedCode)) {
			$page->addError("No code submitted.");
			$can_proceed = false;
		}
		elseif (!preg_match('/^[0-9]{6}$/', $userSubmittedCode)) {
			$page->addError("Invalid code format. Must be 6 digits.");
			$can_proceed = false;
	    }
		else {
			$tfa->setSecret($userStoredSecret);
			if ($tfa->verifyCode($userSubmittedCode)) {
				$page->appendSuccess("Verification successful, please wait...");
				$isVerified = true;

				// Update session state only
				$GLOBALS['_SESSION_']->update(array('otp_verified' => true));

				// Clear the refer_url to allow access to the target page
				$GLOBALS['_SESSION_']->update(array('refer_url' => null));

				// Audit successful OTP verification
				$customer->auditRecord('OTP_VERIFIED', 'OTP code verified successfully');
			}
			else {
				$page->addError("Invalid code");
				$can_proceed = false;
			}
		}
  	}

	// Generate QR code as data URI for img tag (always generate if needed for template)
	$qrCodeData = $tfa->getQRCodeImage($customer->code);

	// Save the target URL in the session if a new one is provided
	$target = $_REQUEST['target'] ?? null;
	if (isset($target) && !empty($target)) {
		// Validate the target URL to prevent potential security issues
		if (!filter_var($target, FILTER_VALIDATE_URL) && !preg_match('/^\/[a-zA-Z0-9\/_-]*$/', $target)) {
			$page->addError("Invalid target URL format");
		}
		else {
			$GLOBALS['_SESSION_']->update(array('refer_url' => $target));
			$page->appendSuccess("Target URL saved for redirection after verification.");
		}
	}
}
