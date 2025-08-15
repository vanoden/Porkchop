<?php
$page = new \Site\Page();
$page->title = "Reset Two-Factor Authentication";
$page->addBreadcrumb("Login", "/_register/login");
$page->addBreadcrumb("Two-Factor Authentication", "/_register/otp");
$page->addBreadcrumb("Reset 2FA", "/_register/reset_otp");
?>
<contentblock>
  <section class="register-reset-otp-section">
    <div class="pageSect_full pad_vert-sm">
      <h1>Reset Two-Factor Authentication</h1>
      <?= $page->showBreadcrumbs() ?>
      <?= $page->showMessages() ?>
    </div>
    <div class="pageSect_full pad_vert-sm">
      <ul class="connectBorder infoText">
        <li>Your recovery link is valid. To continue resetting your two-factor authentication, click the button below. You will be guided through setting up a new authenticator app.</li>
      </ul>
    </div>
    <div class="pageSect_full pad_vert-sm register-reset-otp-center">
      <form method="get" action="/_register/otp">
        <button type="submit" class="button">Continue</button>
      </form>
    </div>
    <div class="pageSect_full pad_vert-sm register-reset-otp-center">
      <a href="/_register/login">&larr; Return to Login</a>
    </div>
  </section>
</contentblock> 