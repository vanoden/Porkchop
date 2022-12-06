<?php
	# End Session
	$GLOBALS['_SESSION_']->end();

	# Bounce to Home Page
	header("location: ".PATH."/_register/login");
