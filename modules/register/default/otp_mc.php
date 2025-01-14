<?php
$page = new \Site\Page();
require THIRD_PARTY . '/autoload.php';

use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class TwoFactorAuth {

  private $secretKey;
  private $totp;

  public function __construct($userSecret = null, $username = null, $issuer = null) {
    $issuer = $issuer ?? $GLOBALS['_config']->site->name;
    // Generate or use existing secret key
    $this->secretKey = $userSecret ?? $this->generateSecret();
    $this->totp = TOTP::create($this->secretKey);

    // Set parameters for Google Authenticator compatibility
    $this->totp->setDigits(6);
    $this->totp->setPeriod(30);
    
    // Set label and issuer if provided
    if ($username) {
      $this->totp->setLabel($username);
      $this->totp->setIssuer($issuer);
    }
  }

  /**
   * Generate a random secret key
   */
  private function generateSecret($length = 32) {
    $secret = random_bytes($length);
    return Base32::encodeUpper($secret);
  }

  /**
   * Get the secret key
   */
  public function getSecret() {
    return $this->secretKey;
  }

  /**
   * Generate QR code URL for Google Authenticator
   */
  public function getQRCodeUrl($username, $issuer = 'YourApp') {
    $this->totp->setLabel($username);
    $this->totp->setIssuer($issuer);
    return $this->totp->getProvisioningUri();
  }

  /**
   * Generate QR code as base64 encoded image data
   */
  public function getQRCodeImage($username, $issuer = 'YourApp') {
    $qrUrl = $this->getQRCodeUrl($username, $issuer);

    // Create basic QR code
    $qrCode = new QrCode($qrUrl);

    // Create writer and generate PNG
    $writer = new PngWriter();
    $result = $writer->write($qrCode);

    // Get data URI
    return $result->getDataUri();
  }

  /**
   * Verify the TOTP code
   */
  public function verifyCode($code) {
    return $this->totp->verify($code);
  }
}

// get the secret key from the database
$tfa = new TwoFactorAuth(null, $GLOBALS['_SESSION_']->customer->code, $issuer);
$userStoredSecret = $GLOBALS['_SESSION_']->customer->secret_key;

// First time setup if no secret 2FA key is found
if (empty($userStoredSecret)) {
  $secret = $tfa->getSecret();
  $GLOBALS['_SESSION_']->customer->update(array('secret_key' => $secret));
}

// Verification
$verificationMessage = "";
$isVerified = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $userSubmittedCode = $_POST['verification_code'];
  $tfa = new TwoFactorAuth($userStoredSecret, $GLOBALS['_SESSION_']->customer->code, $issuer);
  if (isset($userSubmittedCode) && !is_null($userSubmittedCode)) {
    if ($tfa->verifyCode($userSubmittedCode)) {
      $page->appendSuccess("Verification successful, please wait...");
      $isVerified = true;
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
