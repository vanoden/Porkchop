<?php
$page = new \Site\Page();
require THIRD_PARTY . '/autoload.php';

use OTPHP\TOTP;


print_r($GLOBALS['_SESSION_']->customer);


if (!empty($GLOBALS['_SESSION_']->customer->login)) {

  $totp = TOTP::generate($GLOBALS['_config']->otp->secret);
  $totp->setPeriod(60);
  $totp->setLabel($GLOBALS['_SESSION_']->customer->login . "@" . $GLOBALS['_config']->otp->label);
  $goqr_me = $totp->getQrCodeUri($GLOBALS['_config']->otp->uri, '[DATA]');
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'];

    // Validate OTP and set cookie if valid and redirect to final target
    if (!$totp->verify($otp)) {
      setcookie($GLOBALS['_config']->otp->cookie, $totp->getSecret(), time() + (86400 * 30), "/");
      if (empty($GLOBALS['_SESSION_']->otp_redirect)) $GLOBALS['_SESSION_']->otp_redirect = "/";
      header("Location: " . PATH . $GLOBALS['_SESSION_']->otp_redirect);
      exit;
    } else {
      $page->addError('Invalid OTP code. Please try again.');
    }
  } else {
    $page->success = "Please enter your OTP code";
  }
} else {
  $page->addError('Please login with your username and password first');
}
