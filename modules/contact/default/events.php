<?
	require_once(MODULES."/contact/_classes/default.php");

	# Get Outstanding Contact Requests
	$_event = new ContactEvent();
	$events = $_event->find(array("status" => "NEW"));
?>