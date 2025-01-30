<?php
$page = new \Site\Page();

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
  $userSubmittedCode = $_POST['verification_code'];
  $tfa->setSecret( $userStoredSecret );
  if (isset($userSubmittedCode) && !is_null($userSubmittedCode)) {
    if ($tfa->verifyCode($userSubmittedCode)) {
      $page->appendSuccess("Verification successful, please wait...");
      $isVerified = true;
      $GLOBALS['_SESSION_']->update(array('refer_url' => null));

    } else {
      $page->addError("Invalid code");
    }
  } else {
    $page->addError("No code submitted.");
  }
}

// Generate QR code as data URI for img tag
$qrCodeData = $tfa->getQRCodeImage($GLOBALS['_SESSION_']->customer->code);

// Save the target URL in the session if a new one is provided
if (isset($_REQUEST['target']) && !empty($_REQUEST['target'])) $GLOBALS['_SESSION_']->update(array('refer_url' => $_REQUEST['target']));
