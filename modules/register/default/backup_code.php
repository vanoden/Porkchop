<?php
$page = new \Site\Page();
$page->title = "Login with Backup Code";
$page->addBreadcrumb("Login", "/_register/login");
$page->addBreadcrumb("Backup Code", "/_register/backup_code");
?>
<contentblock>
  <section style="margin-top: 0;">
    <div class="pageSect_full pad_vert-sm">
      <h1>Login with Backup Code</h1>
      <?= $page->showBreadcrumbs() ?>
      <?= $page->showMessages() ?>
    </div>
    <div class="pageSect_full pad_vert-sm">
      <form method="post" action="/_register/backup_code" class="pageSect_full">
        <label for="backup_code"><strong>Backup Code</strong></label><br>
        <input type="text" id="backup_code" name="backup_code" required placeholder="Enter your backup code" class="long-field" style="max-width: 350px; margin-bottom: 1em;">
        <br>
        <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
        <input type="submit" class="button" value="Login with Backup Code">
      </form>
    </div>
    <div class="pageSect_full pad_vert-sm" style="text-align: center;">
      <a href="/_register/login">&larr; Return to Login</a>
    </div>
  </section>
</contentblock> 