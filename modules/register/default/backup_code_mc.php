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
                
                // DEBUG: Log backup code login process
                app_log("=== BACKUP CODE LOGIN ===", 'debug', __FILE__, __LINE__, 'otplogs');
                app_log("User ID: " . $user_id, 'debug', __FILE__, __LINE__, 'otplogs');
                app_log("Session assigned, secret key was cleared - user will need to set up 2FA again", 'debug', __FILE__, __LINE__, 'otplogs');
                                
                // Send backup code used notification email
                $customer = new \Register\Customer($user_id);
                $emailResult = $customer->sendBackupCodeUsedNotification();
                if (!$emailResult) {
                    app_log("Failed to send backup code notification: " . $customer->error(), 'warn', __FILE__, __LINE__, 'otplogs');
                } else {
                    app_log("Backup code notification email sent successfully", 'info', __FILE__, __LINE__, 'otplogs');
                }
                
                $page->appendSuccess("Backup code accepted. Please set up two-factor authentication again.");
                header("Location: /_register/otp");
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
