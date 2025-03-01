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
        header("HTTP/1.0 404 Not Found");
        exit;
    }
} else {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Create the Customer object with ID
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

// Get the list of user contacts
$contacts = $customer->contacts();

// Set content type for vCard
header('Content-Type: text/x-vcard');
header('Content-Disposition: attachment; filename="' . $customer->first_name . '_' . $customer->last_name . '.vcf"');

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
exit; 