<?php
$page = new \Site\Page();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
        $page->addError("Invalid request");
        return;
    }

    $email_address = trim($_POST['email_address'] ?? '');
    
    // Validate email format
    if (empty($email_address)) {
        $page->addError("Email address is required");
    }
    elseif (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
        $page->addError("Please enter a valid email address");
    }
    else {
        // Look up user by email
        $contact = new \Register\Contact();
        $contact->getSingleContact('email', $email_address);
        $contact->getPerson();
        if (is_object($contact->person) && $contact->person->id) {
            $customer = new \Register\Customer($contact->person->id);
        } else {
            $customer = null;
        }

        if ($contact->error()) {
            app_log("Error finding contact: " . $contact->error(), 'error', __FILE__, __LINE__);
            $page->addError("Error processing request, please try again later");
        }
        elseif ($contact->person && $contact->person->id && $customer) {
            if ($customer->error()) {
                app_log("Error loading customer: " . $customer->error(), 'error', __FILE__, __LINE__);
                $page->addError("Error processing request, please try again later");
            }
            elseif (!$customer->id) {
                // Don't reveal whether email exists - redirect to success page
                app_log("OTP recovery requested for non-existent customer with email: " . $email_address, 'notice', __FILE__, __LINE__);
                header("Location: /_register/otp_recovery_sent");
                exit;
            }
            elseif (empty($customer->otp_secret_key())) {
                // User doesn't have 2FA enabled - redirect to success page
                app_log("OTP recovery requested for customer without 2FA: " . $customer->code, 'notice', __FILE__, __LINE__);
                header("Location: /_register/otp_recovery_sent");
                exit;
            }
            else {
                // Valid customer with 2FA - send recovery email
                $result = $customer->sendOTPRecovery($email_address);
                
                if ($result) {
                    app_log("OTP recovery email sent to: " . $email_address . " for customer: " . $customer->code, 'notice', __FILE__, __LINE__);
                    header("Location: /_register/otp_recovery_sent");
                    exit;
                }
                else {
                    $page->addError("Error sending recovery email, please try again later");
                    app_log("Failed to send OTP recovery email: " . $customer->error(), 'error', __FILE__, __LINE__);
                }
            }
        }
        else {
            // Email not found - redirect to success page to prevent enumeration
            app_log("OTP recovery requested for unknown email: " . $email_address, 'notice', __FILE__, __LINE__);
            header("Location: /_register/otp_recovery_sent");
            exit;
        }
    }
}

// Set page properties
$page->title = "Recover Two-Factor Authentication";
$page->addBreadcrumb("Login", "/_register/login");
$page->addBreadcrumb("Two-Factor Authentication", "/_register/otp");
$page->addBreadcrumb("Recover 2FA", "/_register/recover_otp"); 