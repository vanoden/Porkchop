<?php
###################################################
## admin_account_password_mc.php				###
## This program handles the password tab for	###
## customer account management.				###
## A. Caravello 11/12/2002						###
###################################################

$page = new \Site\Page(array("module" => 'register', "view" => 'account'));
$page->requirePrivilege('manage customers');
$page->setAdminMenuSection("Customer");  // Keep Customer section open
$customer = new \Register\Customer();

if (isset($_REQUEST['customer_id']) && preg_match('/^\d+$/', $_REQUEST['customer_id']))
	$customer_id = $_REQUEST['customer_id'];
elseif (preg_match('/^[\w\-\.\_]+$/', $GLOBALS['_REQUEST_']->query_vars_array[0])) {
	$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
	$customer->get($code);
	if ($customer->id)
		$customer_id = $customer->id;
	else
		$page->addError("Customer not found");
} else
	$customer_id = $GLOBALS['_SESSION_']->customer->id;

app_log($GLOBALS['_SESSION_']->customer->code . " accessing account of customer " . $customer_id, 'notice', __FILE__, __LINE__);

#######################################
## Handle Actions					###
#######################################

/** @section Apply Changes
 * This section handles the form submission for applying changes to the customer account.
 * It validates the input, updates the customer information, and handles password changes.
 */
if (isset($_REQUEST['method']) && $_REQUEST['method'] == "Apply") {

	// Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
	if (!isset($_POST['csrfToken']) || !$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
		$page->addError("Invalid request");
		return 403;
	} else {
		app_log("Password form submitted", 'debug', __FILE__, __LINE__);
		
		// Validate password fields
		if (isset($_REQUEST["password"]) && isset($_REQUEST["password_2"])) {
			if ($_REQUEST["password"] != $_REQUEST["password_2"]) {
				$page->addError("Passwords do not match");
				goto load;
			}
			
			if (strlen($_REQUEST["password"]) < 6) {
				$page->addError("Password must be at least 6 characters long");
				goto load;
			}
			
			// Change the password
			if ($customer_id) {
				app_log("Changing password for customer " . $customer_id, 'debug', __FILE__, __LINE__);
				$customer = new \Register\Customer($customer_id);
				
				if (!$customer->changePassword($_REQUEST["password"])) {
					$page->addError("Password needs more complexity");
				} else {
					$page->appendSuccess("Password changed successfully.");
					
					// Send password reset notification email when admin changes password
					if ($customer && $customer->id) {
						$to_email = $customer->notify_email();
						if (!empty($to_email) && isset($GLOBALS['_config']->register->password_reset_notification)) {
							$email_config = $GLOBALS['_config']->register->password_reset_notification;
							if (isset($email_config->template) && file_exists($email_config->template)) {
								$template = new \Content\Template\Shell(
									array(
										'path' => $email_config->template,
										'parameters' => array(
											'CUSTOMER.FIRST_NAME' => $customer->first_name,
											'CUSTOMER.LOGIN' => $customer->code,
											'RESET.DATE' => date('Y-m-d'),
											'RESET.TIME' => date('H:i:s T'),
											'SUPPORT.EMAIL' => $GLOBALS['_config']->site->support_email,						
											'LOGIN.URL' => 'http' . ($GLOBALS['_config']->site->https ? 's' : '') . '://' . $GLOBALS['_config']->site->hostname . '/_register/login',
											'COMPANY.NAME' => $GLOBALS['_SESSION_']->company->name ?? 'Spectros Instruments'
										)
									)
								);

								if (!$template->error()) {
									$message = new \Email\Message();
									$message->html(true);
									$message->to($to_email);
									$message->from($email_config->from);
									$message->subject($email_config->subject);
									$message->body($template->output());

									$transportFactory = new \Email\Transport();
									$transport = $transportFactory->Create(array('provider' => $GLOBALS['_config']->email->provider));
									if ($transport && !$transport->error()) {
										$transport->hostname($GLOBALS['_config']->email->hostname);
										$transport->token($GLOBALS['_config']->email->token);
										if (!$transport->deliver($message)) {
											app_log("Error sending password reset notification email: " . $transport->error(), 'error', __FILE__, __LINE__);
										} else {
											app_log("Password reset notification email sent to " . $to_email, 'info', __FILE__, __LINE__);
											$customer->auditRecord('PASSWORD_RESET_NOTIFICATION_SENT', 'Password reset notification email sent to: ' . $to_email);
										}
									} else {
										app_log("Error creating email transport for password reset notification: " . ($transport ? $transport->error() : 'Transport creation failed'), 'error', __FILE__, __LINE__);
									}
								} else {
									app_log("Error generating password reset notification email: " . $template->error(), 'error', __FILE__, __LINE__);
								}
							} else {
								app_log("Password reset notification email template not found", 'error', __FILE__, __LINE__);
							}
						} else {
							if (empty($to_email)) {
								app_log("No email address available for customer " . $customer->id, 'error', __FILE__, __LINE__);
							} else {
								app_log("Password reset notification email configuration not found", 'error', __FILE__, __LINE__);
							}
						}
					} else {
						app_log("Invalid customer object for password reset notification", 'error', __FILE__, __LINE__);
					}
				}
			} else {
				$page->addError("Invalid customer ID");
			}
		} else {
			$page->addError("Password fields are required");
		}
	}
}

load:
if ($customer_id) {
	$customer = new \Register\Customer($customer_id);
}
$rolelist = new \Register\RoleList();
$all_roles = $rolelist->find();
$_department = new \Register\Department();
$departments = $_department->find();
app_log("Loading Organizations", 'trace', __FILE__, __LINE__);
$organizationlist = new \Register\OrganizationList();
$organizations = $organizationlist->find();
$_contact = new \Register\Contact();
$contact_types = $_contact->types;

if (!isset($target)) $target = '';

$page->title = "Customer Account Details - Change Password";
$page->addBreadcrumb("Customer");
$page->addBreadcrumb("Organizations", "/_register/organizations");
$organization = $customer->organization();
if (isset($organization->id)) $page->addBreadcrumb($organization->name, "/_register/admin_organization?id=" . $organization->id);
if (isset($customer->id)) $page->addBreadcrumb($customer->full_name(), "/_register/admin_account?customer_id=" . $customer->id);
