<?php
$page = new \Site\Page();
$can_proceed = true;

$page->requireRole('developer testing only');

// Return 404 to exclude from testing for now
header("HTTP/1.0 404 Not Found");
exit;

// get the secret key from the database
$tfa = new \Register\AuthenticationService\TwoFactorAuth(null, $GLOBALS['_SESSION_']->customer->code, $GLOBALS['_config']->site->hostname);
$userStoredSecret = $GLOBALS['_SESSION_']->customer->secret_key;

// First time setup if no secret 2FA key is found
if (empty($userStoredSecret)) {
  $secret = $tfa->getSecret();
  $GLOBALS['_SESSION_']->customer->update(array('secret_key' => $secret));
}

// Verification
$isVerified = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $userSubmittedCode = $_POST['verification_code'] ?? null;
  
  // Validate the verification code
  if (empty($userSubmittedCode)) {
    $page->addError("No code submitted.");
    $can_proceed = false;
  } elseif (!preg_match('/^[0-9]{6}$/', $userSubmittedCode)) {
    $page->addError("Invalid code format. Must be 6 digits.");
    $can_proceed = false;
  } else {
    $tfa->setSecret($userStoredSecret);
    if ($tfa->verifyCode($userSubmittedCode)) {
      $page->appendSuccess("Verification successful, please wait...");
      $isVerified = true;
      $GLOBALS['_SESSION_']->update(array('refer_url' => null));
    } else {
      $page->addError("Invalid code");
      $can_proceed = false;
    }
  }
}

// Generate QR code as data URI for img tag
if ($can_proceed) {
  $qrCodeData = $tfa->getQRCodeImage($GLOBALS['_SESSION_']->customer->code);
}

// Save the target URL in the session if a new one is provided
$target = $_REQUEST['target'] ?? null;
if (isset($target) && !empty($target)) {
  // Validate the target URL to prevent potential security issues
  if (!filter_var($target, FILTER_VALIDATE_URL) && !preg_match('/^\/[a-zA-Z0-9\/_-]*$/', $target)) {
    $page->addError("Invalid target URL format");
  } else {
    $GLOBALS['_SESSION_']->update(array('refer_url' => $target));
  }
}
