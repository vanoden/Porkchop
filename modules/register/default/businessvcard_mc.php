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

// Format structured name: Last;First
echo "N:{$customer->last_name};{$customer->first_name};;;\n";

// Format display name
echo "FN:{$customer->first_name} {$customer->last_name}\n";

// Job Title/Role if available
$job_title = $customer->getMetadata('job_title');
if (!empty($job_title)) {
    echo "ROLE:{$job_title}\n";
    echo "TITLE:{$job_title}\n";
}

// Organization
echo "ORG:{$customer->organization()->name}\n";

// Timezone if available
if (!empty($customer->timezone)) {
    echo "TZ:{$customer->timezone}\n";
}

// Job Description as a note if available
$job_description = $customer->getMetadata('job_description');
if (!empty($job_description)) {
    echo "NOTE:{$job_description}\n";
}

// Organization website if available
$organization = $customer->organization();
if (!empty($organization->website_url)) {
    echo "URL:{$organization->website_url}\n";
}

// Get customer locations for address information
$locations = $customer->locations();
if (!empty($locations) && is_array($locations)) {
    foreach ($locations as $location) {
        if (!empty($location->street) || !empty($location->city) || !empty($location->state) || !empty($location->zip)) {
            echo "ADR;TYPE=work:;;{$location->street};{$location->city};{$location->state};{$location->zip};{$location->country}\n";
            break; // Just use the first location
        }
    }
}

// Loop through contacts to add phone numbers, email, and other contact info
foreach ($contacts as $contact) {
    if ($contact->public) {
        switch ($contact->type) {
            case 'phone':
                echo "TEL;TYPE=WORK,VOICE:{$contact->value}\n";
                break;
            case 'email':
                echo "EMAIL;TYPE=WORK:{$contact->value}\n";
                break;
            case 'sms':
                echo "TEL;TYPE=CELL:{$contact->value}\n";
                break;
            case 'facebook':
                echo "URL;TYPE=FACEBOOK:{$contact->value}\n";
                break;
            case 'insite':
                echo "X-INSITE-MESSAGE:{$contact->value}\n";
                break;
            default:
                // For any other contact types, add as a custom field
                $typeUpper = strtoupper($contact->type);
                echo "X-{$typeUpper}:{$contact->value}\n";
                break;
        }
    }
}

echo "END:VCARD\n";
exit; 