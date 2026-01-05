<style>
.pageContainer {
  min-height: 500px;
}

.password-token-title {
  margin-bottom: 2rem;
}

.password-token-content {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.password-token-content img,
.password-token-secondary img,
.register-password-token-icon {
  flex-shrink: 0;
  max-width: 75px;
}

.password-token-content h3 {
  margin: 0;
}

.password-token-secondary {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-top: 2rem;
}

.password-token-secondary h3 {
  margin-top: 0;
}

@media (max-width: 768px) {
  .password-token-content {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .password-token-secondary {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

<section>
  <h2 class="password-token-title pageSect_full">Thank you</h2>
  <div class="password-token-content pageSect_full">
    <img src="/img/icons/icon_portal_email.png" class="register-password-token-icon">
    <div>
      <h3>Check your email</h3>
      <p>If an account exists with the address you entered, you will receive an email with a link to reset your password. This may take a few minutes.</p>
    </div>
  </div>
  <div class="password-token-secondary pageSect_full">
    <img src="/img/icons/icon_portal_spam.png" class="register-password-token-icon">
    <div>
      <h3>Didn't receive an email?</h3>
      <p>Be sure to check your spam folder for any emails from no-reply@spectrosinstruments.com. If you do not receive an email, contact us via <a href="/contact_us.html">our contact form</a> or email directly to our service account, <a href="mailto:service@spectrosinstruments.com">service@spectrosinstruments.com</a>.</p>
    </div>
  </div>
</section>
