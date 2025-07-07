<?php
namespace Register\AuthenticationService;

require THIRD_PARTY . '/autoload.php';

use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class TwoFactorAuth {

    private $secretKey;
    private $totp;
    private $issuer;
    private $hostname;
    private $username;

    public function __construct($userSecret = null, $username = null, $hostname = null) {

        $this->hostname = $hostname ?? null;
        $this->username = $username ?? null;

        // Generate or use existing secret key
        $this->secretKey = $userSecret ?? $this->generateSecret();
        $this->totp = TOTP::create($this->secretKey);

        // Set parameters for Google Authenticator compatibility
        $this->totp->setDigits(6);
        $this->totp->setPeriod(30);

        // Set label and issuer if provided
        if ($this->username) {
            $this->totp->setLabel($this->username);
            $this->totp->setIssuer($this->hostname);
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
     * Set the secret key
     */
    public function setSecret($secret) {
        $this->secretKey = $secret;
        $this->totp = TOTP::create($this->secretKey);
    }

    /**
     * Generate QR code URL for Google Authenticator
     */
    public function getQRCodeUrl() {
        $this->totp->setLabel($this->username);
        $this->totp->setIssuer($this->hostname);
        return $this->totp->getProvisioningUri();
    }

    /**
     * Generate QR code as base64 encoded image data
     */
    public function getQRCodeImage() {
        $qrUrl = $this->getQRCodeUrl();

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