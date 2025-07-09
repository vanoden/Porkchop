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
        // DEBUG: Log constructor parameters
        app_log("=== TwoFactorAuth::__construct() ===", 'debug', __FILE__, __LINE__);
        app_log("userSecret: " . ($userSecret ? substr($userSecret, 0, 10) . "..." : 'null'), 'debug', __FILE__, __LINE__);
        app_log("username: " . ($username ?: 'null'), 'debug', __FILE__, __LINE__);
        app_log("hostname: " . ($hostname ?: 'null'), 'debug', __FILE__, __LINE__);

        $this->hostname = $hostname ?? null;
        $this->username = $username ?? null;

        // Generate or use existing secret key
        $this->secretKey = $userSecret ?? $this->generateSecret();
        app_log("Final secret key: " . substr($this->secretKey, 0, 10) . "...", 'debug', __FILE__, __LINE__);
        app_log("Secret key length: " . strlen($this->secretKey), 'debug', __FILE__, __LINE__);
        
        $this->totp = TOTP::create($this->secretKey);
        app_log("TOTP instance created", 'debug', __FILE__, __LINE__);

        // Set parameters for Google Authenticator compatibility
        $this->totp->setDigits(6);
        $this->totp->setPeriod(30);
        app_log("TOTP configured - digits: 6, period: 30", 'debug', __FILE__, __LINE__);

        // Set label and issuer if provided
        if ($this->username) {
            $this->totp->setLabel($this->username);
            $this->totp->setIssuer($this->hostname);
            app_log("TOTP label set to: " . $this->username, 'debug', __FILE__, __LINE__);
            app_log("TOTP issuer set to: " . $this->hostname, 'debug', __FILE__, __LINE__);
        }
        
        app_log("TwoFactorAuth constructor completed", 'debug', __FILE__, __LINE__);
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
        app_log("TwoFactorAuth::getSecret() returning: " . substr($this->secretKey, 0, 10) . "...", 'debug', __FILE__, __LINE__);
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
        // DEBUG: Log verification attempt
        app_log("=== TwoFactorAuth::verifyCode() ===", 'debug', __FILE__, __LINE__);
        app_log("Input code: " . $code, 'debug', __FILE__, __LINE__);
        app_log("Secret key: " . substr($this->secretKey, 0, 10) . "...", 'debug', __FILE__, __LINE__);
        app_log("Secret key length: " . strlen($this->secretKey), 'debug', __FILE__, __LINE__);
        
        // Get current time and expected codes
        $currentTime = time();
        $currentCode = $this->totp->now();
        app_log("Current time: " . $currentTime, 'debug', __FILE__, __LINE__);
        app_log("Current expected code: " . $currentCode, 'debug', __FILE__, __LINE__);
        
        // Use a window of 1 (current time ± 30 seconds) for better compatibility
        $result = $this->totp->verify($code, null, 1);
        app_log("TOTP verify result: " . ($result ? 'true' : 'false'), 'debug', __FILE__, __LINE__);
        
        return $result;
    }

    /**
     * Get the current TOTP code
     */
    public function getCurrentCode() {
        $code = $this->totp->now();
        app_log("TwoFactorAuth::getCurrentCode() returning: " . $code, 'debug', __FILE__, __LINE__);
        return $code;
    }
    
    /**
     * Get TOTP code for a specific time
     */
    public function getCode($timestamp) {
        $code = $this->totp->at($timestamp);
        app_log("TwoFactorAuth::getCode(" . $timestamp . ") returning: " . $code, 'debug', __FILE__, __LINE__);
        return $code;
    }
}