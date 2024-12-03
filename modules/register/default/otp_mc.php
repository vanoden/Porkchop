<?php
$page = new \Site\Page();
require THIRD_PARTY . '/autoload.php';

use OTPHP\TOTP;
if (!empty($GLOBALS['_SESSION_']->customer->code)) {

  // Save the target URL in the session if a new one is provided
  if (isset($_REQUEST['target']) && !empty($_REQUEST['target'])) $GLOBALS['_SESSION_']->update(array('refer_url' => $_REQUEST['target']));

  $totp = TOTP::generate($GLOBALS['_config']->otp->secret);
  $totp->setPeriod(60);
  $totp->setLabel($GLOBALS['_SESSION_']->customer->code . "@" . $GLOBALS['_config']->otp->label);
  $goqr_me = $totp->getQrCodeUri($GLOBALS['_config']->otp->uri, '[DATA]');

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'];

    // Validate OTP and set cookie if valid and redirect to final target
    // @TODO - fix the verification logic, it seems to always return false
    if (!$totp->verify($otp)) {
      setcookie($GLOBALS['_config']->otp->cookie, $totp->getSecret(), time() + (86400 * 30), "/");
      header("Location: " . PATH . $GLOBALS['_SESSION_']->refer_url);
      $GLOBALS['_SESSION_']->update(array('refer_url' => ''));
      exit;
    } else {
      $page->addError('Invalid OTP code. Please try again.');
    }

  } else {
    $page->success = "Please enter your OTP code";
  }

} else {
  $page->addError('Please login with your username and password first [<a href="/_register/login">Login</a>]');
}
