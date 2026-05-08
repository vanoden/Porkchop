<contentblock>
  <section class="register-otp-recovery-sent-section">

    <div class="pageSect_full pad_vert-sm">
      <h1>Recover Two-Factor Instructions Sent</h1>
      <?= $page->showBreadcrumbs() ?>
      <?= $page->showMessages() ?>
    </div>

    <div class="pageSect_full pad_vert-sm">
      <ul class="connectBorder infoText">
        <li>If an account exists with the address you entered and has two-factor authentication enabled, you will receive an email with instructions to reset your 2FA setup. This may take a few minutes.</li>
      </ul>
    </div>

    <div class="pageSect_half">
      <img src="/img/icons/icon_portal_email.png" class="iconButton register-otp-recovery-sent-icon">
      <strong>Check your email</strong>
      <p>Look for a message with a recovery link. Our emails are sent from <strong>no-reply@spectrosinstruments.com</strong>. To ensure you receive this email, please add this address to your email address book or contacts. If you don't see it right away, check your spam, junk, or bulk mail folders.</p>
    </div>
    <div class="pageSect_half">
      <img src="/img/icons/icon_portal_spam.png" class="iconButton register-otp-recovery-sent-icon">
      <strong>Didn't receive an email?</strong>
      <p>Our emails are sent from <strong>no-reply@spectrosinstruments.com</strong>. Please:</p>
      <ul style="margin: 0.5rem 0; padding-left: 1.5rem;">
        <li>Add <strong>no-reply@spectrosinstruments.com</strong> to your email address book or contacts</li>
        <li>Check your spam, junk, or bulk mail folders</li>
        <li>Wait a few minutes for the email to arrive</li>
      </ul>
      <p>If you still do not receive an email, contact us via <a href="/contact_us.html">our contact form</a> or email <a href="mailto:service@spectrosinstruments.com">service@spectrosinstruments.com</a>.</p>
    </div>

    <div class="pageSect_full pad_vert-sm register-otp-recovery-sent-center">
      <a href="/_register/login">&larr; Return to Login</a>
    </div>
  </section>
</contentblock>