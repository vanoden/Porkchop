<contentblock>
    <section class="register-recover-otp-section">
        <div class="pageSect_full pad_vert-sm">
            <h1>Recover Two-Factor Authentication</h1>
            <?= $page->showBreadcrumbs() ?>
            <?= $page->showMessages() ?>
        </div>

        <div class="pageSect_full pad_vert-sm">
            <ul class="connectBorder infoText">
                <li>If you have lost access to your authenticator app, enter your email address below. If your account has two-factor authentication enabled, you will receive instructions to reset your 2FA setup.</li>
            </ul>
        </div>

        <div class="pageSect_half">
            <h2>How it works</h2>
            <ul>
                <li>Enter your email address and submit the form.</li>
                <li>If your account is eligible, you'll receive a recovery email.</li>
                <li>The email contains a link to reset your 2FA and set up a new authenticator app.</li>
                <li>If you do not receive an email, check your spam folder or contact support.</li>
            </ul>
        </div>
        <div class="pageSect_half">
            <form method="post" action="/_register/recover_otp" class="pageSect_full">
                <label for="email_address"><strong>Email Address</strong></label><br>
                <input type="email"
                    id="email_address"
                    name="email_address"
                    required
                    placeholder="Enter your email address"
                    value="<?= htmlspecialchars($_POST['email_address'] ?? '') ?>"
                    class="long-field"
                    class="register-recover-otp-email-input">
                <br>
                <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
                <input type="submit" class="button" value="Send Recovery Instructions">
            </form>
        </div>

        <div class="pageSect_full pad_vert-sm register-recover-otp-center">
            <a href="/_register/otp">&larr; Back to Two-Factor Authentication</a>
        </div>
</contentblock>