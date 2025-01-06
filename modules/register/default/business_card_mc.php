<?php
$site = new \Site();
$page = $site->page();

// Check if a customer_id is provided
if (isset($_REQUEST['customer_id']) && preg_match('/^\d+$/', $_REQUEST['customer_id'])) {
	$customer = new \Register\Customer($_REQUEST['customer_id']);
} elseif ($GLOBALS['_SESSION_']->customer->id) {
	$customer = new \Register\Customer($GLOBALS['_SESSION_']->customer->id);
} else {
	// Send a 404 HTTP response
	header("HTTP/1.0 404 Not Found");
	exit;
}

// Check if the customer's profile is public
if ($customer->profile !== 'public') {
	// Send a 404 HTTP response
	header("HTTP/1.0 404 Not Found");
	exit;
}

// Get the list of user contacts
$contacts = $customer->contacts();

// Check if vcard=show is present in the URL
if (isset($_GET['vcard']) && $_GET['vcard'] === 'show') {
	// Output the vCard data
	echo "BEGIN:VCARD\n";
	echo "VERSION:3.0\n";
	echo "FN:{$customer->first_name} {$customer->last_name}\n";
	echo "ORG:{$customer->organization()->name}\n";

	// Loop through contacts to add phone numbers and email
	foreach ($contacts as $contact) {
		if ($contact->public) {
			if ($contact->type === 'phone') {
				echo "TEL;TYPE=WORK,VOICE:{$contact->value}\n";
			} elseif ($contact->type === 'email') {
				echo "EMAIL:{$contact->value}\n";
			}
		}
	}

	echo "END:VCARD\n";
	exit; // Stop further processing
}
