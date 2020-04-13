<?php
	require_module('session');

	$_session = new Session();
	$sessions = $_session->find(
		array(
			'date_start'	=> date('Y-m-d H:i:s', time() - 300)
		)
	);

	if ($_session->error)
		$GLOBALS['_page']->error = "Error loading active sessions: ".$_session->error;
