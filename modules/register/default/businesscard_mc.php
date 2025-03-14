<?php
$site = new \Site();
$page = $site->page();

// Create the Customer object
$customer = new \Register\Customer();

// Determine the customer_id
if (isset($_REQUEST['customer_id']) && preg_match('/^\\d+$/', $_REQUEST['customer_id'])) {
	$customer_id = $_REQUEST['customer_id'];
} elseif (preg_match('/^[\\w\\-\\.\\_]+$/', $GLOBALS['_REQUEST_']->query_vars_array[0])) {
	$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
	$customer->get($code);
	if ($customer->id) {
		$customer_id = $customer->id;
	} else {
		$page->addError("Customer not found");
		header("HTTP/1.0 404 Not Found");
		exit;
	}
} else {
	$customer_id = $GLOBALS['_SESSION_']->customer->id;
}

// Create the Customer object
if ($customer_id) {
	$customer = new \Register\Customer($customer_id);
} else {
	header("HTTP/1.0 404 Not Found");
	exit;
}

// Check if the customer's profile is public
if ($customer->profile !== 'public') {
	header("HTTP/1.0 404 Not Found");
	exit;
}

// Get profile image information
$profileImage = array(
    'src' => '/img/_global/icon_myaccount.svg',
    'alt' => 'personal profile photo'
);

$defaultImageId = $customer->getMetadata('default_image');
if ($defaultImageId) {
    $defaultImage = new \Storage\File($defaultImageId);
    if ($defaultImage->id) {
        $profileImage = array(
            'src' => "/_storage/downloadfile?file_id=" . $defaultImageId,
            'alt' => htmlspecialchars($customer->first_name . ' ' . $customer->last_name) . "'s profile photo"
        );
    }
}

// Get the list of user contacts
$contacts = $customer->contacts();

// get the organization
$organization = $customer->organization();
