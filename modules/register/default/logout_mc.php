<?PHP
	# End Session
	$GLOBALS['_SESSION_']->end();

	# Delete Cookie
	setcookie("session_code", $GLOBALS['_SESSION_']->session_code, time() - 604800, '/', $GLOBALS['_SESSION_']->domain);

	# Bounce to Home Page
	header("location: ".PATH."/_register/login");
?>
