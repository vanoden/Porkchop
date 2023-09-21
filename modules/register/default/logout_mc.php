<?php
	# End Session
	$GLOBALS['_SESSION_']->end();

	# Bounce to Home Page
	header("Location: ".PATH."/_register/login");
