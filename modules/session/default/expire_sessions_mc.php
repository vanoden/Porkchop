<?php
	require(MODULES."/session/_classes/session.php");
	$_session = new Session();

	# Get Expired Session from Database
	$sessions = $_session->find(array('expired' => 1));
	print_r($sessions);
