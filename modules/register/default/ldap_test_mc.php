<?
	$LDAPServerAddress1 = "192.168.30.11";
	$LDAPServerAddress2 = "192.168.30.12";
	$LDAPServerPort="389";
	$LDAPServerTimeOut ="60";
	$LDAPContainer="dc=turbine,dc=com"; // <- your domain info
	$BIND_username = "TURBINE\\".$_POST['username']; // <- an account in AD to test using
	$BIND_password = $_POST['password'];
	$login_error_code = 0;

	if(($ds=ldap_connect($LDAPServerAddress1)) || ($ds=ldap_connect($LDAPServerAddress2)))
	{
		ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

		if($r=ldap_bind($ds,$BIND_username,$BIND_password))
		{
			print "Success!";
		}
		else
		{
			print "Auth Failed: ".ldap_error($ds);
			if (ldap_get_option($ds, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
				echo "Error Binding to LDAP: $extended_error";
			} else {
			    echo "Error Binding to LDAP: No additional information is available.";
			}
		}
	}
?>