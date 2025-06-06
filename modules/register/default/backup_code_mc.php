<?php
$page = new \Site\Page();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
        $page->addError("Invalid request");
    } else {
        $backup_code = trim($_POST['backup_code'] ?? '');
        if (empty($backup_code)) {
            $page->addError("Backup code is required");
        } else {
            // Use Customer class method for backup code login
            $user_id = \Register\Customer::loginWithBackupCode($backup_code);
            if ($user_id) {
                $GLOBALS['_SESSION_']->assign($user_id, false);
                $page->appendSuccess("Backup code accepted. Logging you in...");
                header("Location: /_register/account");
                exit;
            } else {
                $page->addError("Invalid or already used backup code");
            }
        }
    }
}

$page->title = "Login with Backup Code";
$page->addBreadcrumb("Login", "/_register/login");
$page->addBreadcrumb("Backup Code", "/_register/backup_code"); 