<?php
$page = new \Site\Page();
require THIRD_PARTY . '/autoload.php';
use OTPHP\TOTP;

if (!empty($GLOBALS['_SESSION_']->customer->login)) {

    $totp = TOTP::generate($GLOBALS['_config']->otp->secret);
    $totp->setPeriod(60); 
    $totp->setLabel($GLOBALS['_SESSION_']->customer->login ."@". $GLOBALS['_config']->otp->label);
    $goqr_me = $totp->getQrCodeUri($GLOBALS['_config']->otp->uri, '[DATA]');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $otp = $_POST['otp'];
          if ($totp->verify($otp)) {
            $page->success = "Login successful, please wait...";
            setcookie($GLOBALS['_config']->otp->cookie, $totp->getSecret(), time() + (86400 * 30), "/");
          } else {
            $page->addError( 'Invalid OTP code. Please try again.' );
          }
    } else {
        $page->success = "Please enter your OTP code";
    }

} else {
  $page->addError('Please login with your username and password first');
}

echo 'The current OTP is: '.$totp->now();