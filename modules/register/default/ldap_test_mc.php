<?php
	$ldapResult = '';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if (empty($GLOBALS['_SESSION_']) || !$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
			$ldapResult = 'Invalid request';
		}
		elseif (!isset($_POST['username']) || trim((string) $_POST['username']) === '' || !isset($_POST['password'])) {
			$ldapResult = 'Username and password are required';
		}
		elseif (!function_exists('ldap_connect')) {
			$ldapResult = 'LDAP is not available on this server';
		}
		else {
			$LDAPServerAddress1 = "192.168.30.11";
			$LDAPServerAddress2 = "192.168.30.12";
			$BIND_username = "TURBINE\\".$_POST['username'];
			$BIND_password = (string) $_POST['password'];

			if (($ds = ldap_connect($LDAPServerAddress1)) || ($ds = ldap_connect($LDAPServerAddress2))) {
				ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
				ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

				if (ldap_bind($ds, $BIND_username, $BIND_password)) {
					$ldapResult = 'Success!';
				}
				else {
					$ldapResult = 'Auth Failed: '.ldap_error($ds);
					if (ldap_get_option($ds, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
						$ldapResult .= " Error Binding to LDAP: $extended_error";
					}
					else {
						$ldapResult .= ' Error Binding to LDAP: No additional information is available.';
					}
				}
			}
		}
	}
