
<style>
/* Ensure page-level centering */
body, html {
    margin: 0;
    padding: 0;
    width: 100%;
}

.otp-page-wrapper {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    padding: 2rem 1rem;
    box-sizing: border-box;
}

/* OTP Page Styles */
.register-otp-container {
    max-width: 600px;
    margin: 0;
    padding: 2rem;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    width: 100%;
    display: block;
    position: relative;
}

/* Ensure all content within the container is properly contained */
.register-otp-container * {
    max-width: 100%;
    box-sizing: border-box;
}

/* Style only specific elements that need containment - not all elements */
.register-otp-container .breadcrumbs,
.register-otp-container .messages,
.register-otp-container .alert,
.register-otp-container .error,
.register-otp-container .success {
    margin: 0 0 1rem 0;
    padding: 0.5rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    font-size: 0.9rem;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* Form styling is handled by #otp_form selector below */

/* Header content wrapper */
.register-otp-header-content {
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.register-otp-header-content * {
    max-width: 100% !important;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
    white-space: normal !important;
}

.register-otp-container h1 {
    text-align: center;
    color: #082f44;
    margin-bottom: 1.5rem;
    font-family: 'Montserrat', Arial, sans-serif;
    font-size: 1.8rem;
    font-weight: 400;
}

.register-otp-qr-code {
    display: block;
    margin: 1.5rem auto;
    max-width: 200px;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 1rem;
    background: #fff;
}

.register-otp-description {
    text-align: center;
    color: #666;
    font-style: italic;
    margin: 1rem auto;
    max-width: 400px;
}

.register-otp-message {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 1rem;
    margin: 1rem auto;
    text-align: center;
    max-width: 400px;
}

.register-otp-message p {
    margin: 0.5rem 0;
    color: #495057;
}

#otp_form {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

#otp_form p {
    text-align: center;
    margin: 1rem auto 0.5rem;
    color: #495057;
    font-weight: 400;
    max-width: 400px;
}

#verification_code {
    width: 100%;
    max-width: 200px;
    margin: 0 auto;
    padding: 1rem;
    font-size: 1.5rem;
    text-align: center;
    border: 2px solid #ddd;
    border-radius: 6px;
    letter-spacing: 0.5rem;
    font-family: 'Courier New', monospace;
    transition: border-color 0.3s ease;
    display: block;
}

#verification_code:focus {
    outline: none;
    border-color: #0085ad;
    box-shadow: 0 0 0 3px rgba(0, 133, 173, 0.1);
}

#otp_form input[type="submit"] {
    width: 100%;
    max-width: 200px;
    margin: 1rem auto;
    padding: 0.75rem 1.5rem;
    background: #0085ad;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: block;
}

#otp_form input[type="submit"]:hover {
    background: #069AC7;
}

.register-otp-app-links {
    margin-top: 2rem;
    text-align: center;
    border-top: 1px solid #eee;
    padding-top: 1.5rem;
}
.register-otp-app-store {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    min-width: 0;
    flex-wrap: wrap;
}

.register-otp-app-store a {
    display: inline-block;
    transition: transform 0.2s ease;
}

.register-otp-app-store a:hover {
    transform: scale(1.05);
}

.register-otp-app-store img {
    height: 40px;
    width: 135px;
    object-fit: contain;
    border-radius: 4px;
}

.register-otp-recovery-links {
    margin-top: 1.5rem;
    text-align: center;
    border-top: 1px solid #eee;
    padding-top: 1rem;
}

.register-otp-recovery-links p {
    margin: 0.5rem 0;
}

.register-otp-recovery-links a {
    color: #0085ad;
    text-decoration: none;
    font-size: 0.9rem;
}

.register-otp-recovery-links a:hover {
    text-decoration: underline;
}

.register-otp-google-play {
    min-height: 80px;
}

.register-otp-app-store img {
    max-height: 37px;
}


/* Mobile Responsive */
@media (max-width: 768px) {
    .register-otp-container {
        margin: 1rem;
        padding: 1.5rem;
    }
    
    .register-otp-container h1 {
        font-size: 1.5rem;
    }
    
    .register-otp-app-store {
        flex-direction: column;
        align-items: center;
    }
    
    .register-otp-app-store img {
        height: 35px;
        width: 120px;
        object-fit: contain;
    }
    
    #verification_code {
        font-size: 1.2rem;
        letter-spacing: 0.3rem;
        max-height: 40px;
    }
}
</style>

<section id="otp_mc" class="register-otp-container">
    <!-- Page Header Content - Wrapped in container -->
    <div class="register-otp-header-content">
        <?= $page->showBreadcrumbs() ?>
        <?= $page->showMessages() ?>
    </div>
    
    <form id="otp_form" action="/_register/otp" method="post">
        <h1>Two Factor Authentication</h1>
        
        <?php if ($showQRCode) { ?>
            <p>Scan this QR code with <strong>Google Authenticator, Microsoft Authenticator, Authy, 1Password, or any compatible app</strong> to set up two-factor authentication (2FA).</p>
            <img src="<?= $qrCodeData ?>" alt="Scan this QR code" class="register-otp-qr-code">
            <div class="register-otp-description">
                You can use any app that supports TOTP (Time-based One-Time Passwords).
            </div>
        <?php } else { ?>
            <div class="register-otp-message">
                <p>You have a 2FA key set up to login to your account</p>
                <p>Please enter the 6 digit code generated by your authenticator app.</p>
            </div>
        <?php } ?>
        
        <p>Enter the 6 digit code from your authenticator app:</p>
        <input type="text"
            id="verification_code"
            name="verification_code"
            maxlength="6"
            pattern="[0-9]{6}"
            inputmode="numeric"
            autocomplete="one-time-code"
            placeholder="000000">
        <input type="submit" value="Submit" />

        <div class="register-otp-app-links">
            <p>Don't have Google Authenticator?</p>
            <div class="register-otp-app-store">
                <a href="https://apps.apple.com/us/app/google-authenticator/id388497605" target="_blank">
                    <img src="https://tools.applemediaservices.com/api/badges/download-on-the-app-store/black/en-us" alt="Download on the App Store">
                </a>
                <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">
                    <img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png" alt="Get it on Google Play" class="register-otp-google-play">
                </a>
            </div>
        </div>

        <?php if (!$showQRCode) { ?>
            <div class="register-otp-recovery-links">
                <p><a href="/_register/recover_otp">Can't access your authenticator? Recover 2FA setup</a></p>
                <p><a href="/_register/backup_code">Use a backup code</a></p>
            </div>
        <?php } ?>

    </form>
</section>

<script>
    // Auto-submit when 6 digits are entered
    document.getElementById('verification_code').addEventListener('input', function(e) {
        if (this.value.length === 6) document.getElementById('otp_form').submit();
    });

    // Only allow numbers
    document.getElementById('verification_code').addEventListener('keydown', function(e) {
        if ((e.key < '0' || e.key > '9') && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') e.preventDefault();
    });
</script>
<?php
if ($isVerified) {
?>
    <script>
        setTimeout(function() {
            <?php if (empty($GLOBALS['_SESSION_']->refer_url)) $GLOBALS['_SESSION_']->refer_url = "/"; ?>
            window.location.href = "<?= $GLOBALS['_SESSION_']->refer_url ?>";
        }, 1000); // Redirect after 2 seconds
    </script>
<?php
}
?>