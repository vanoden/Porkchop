<?php
/**
 * Handle API Request for support
 * 
 * @namespace communications
 * @author A. Caravello 8/12/2013
 */

/**
 * Load API Objects
 *
 * @var array $_package
 */
$_package = array ("name" => "support","version" => "0.1.0","release" => "2016-12-05" );

app_log ( "Request: " . print_r ( $_REQUEST, true ), 'debug', __FILE__, __LINE__ );

// Call Requested Event
if ($_REQUEST ["method"]) {
	// Call the Specified Method
	$function_name = $_REQUEST ["method"];
	$function_name ();
	exit ();
} // Only Developers Can See The API
elseif (! $GLOBALS ['_SESSION_']->customer->has_role ( 'support manager' )) {
	header ( "location: /_support/home" );
	exit ();
}

/**
 * Just See if Server Is Communicating
 */
function ping() {
	$response = new \HTTP\Response ();
	$response->header->session = $GLOBALS ['_SESSION_']->code;
	$response->header->method = $_REQUEST ["method"];
	$response->header->date = system_time ();
	$response->message = "PING RESPONSE";
	$response->success = 1;

	$_comm = new \Monitor\Communication ();
	$_comm->update ( json_encode ( $response ) );
	api_log ( $response );
	print formatOutput ( $response );
}

/**
 * Add a Request
 */
function addRequest() {
	$request = new \Support\Request ();

	$parameters = array ();
	if (permitted ( 'support manager' )) {
		if ($_REQUEST ['customer']) {
			$customer = new \Register\Customer ();
			$customer->get ( $_REQUEST ['customer'] );
			if ($customer->error) app_error ( "Error getting customer: " . $customer->error, 'error', __FILE__, __LINE__ );
			if (! $customer->id) error ( "Customer not found" );
			$parameters ['customer_id'] = $customer->id;
		}
		if ($_REQUEST ['tech']) {
			$admin = new RegisterAdmin ();
			$admin->get ( $_REQUEST ['admin'] );
			if ($admin->error) app_error ( "Error getting admin: " . $admin->error, 'error', __FILE__, __LINE__ );
			if (! $admin->id) error ( "Tech not found" );
			$parameters ['tech_id'] = $admin->id;
		}
		if ($_REQUEST ['status']) {
			$parameters ['status'] = $_REQUEST ['status'];
		}
	}

	$request->add ( $parameters );
	if ($_request->error) app_error ( "Error adding request: " . $_request->error );

	$response = new \HTTP\Response ();
	$response->success = 1;
	$response->request = $request;

	print formatOutput ( $response );
}

/**
 * Update a Request
 */
function updateRequest() {
	$request = new SupportRequest ();
	$request->get ( $_REQUEST ['code'] );
	if ($request->error) app_error ( "Error finding request: " . $request->error, 'error', __FILE__, __LINE__ );
	if (! $request->id) error ( "Request not found" );

	$request->update ( $request->id, array ('name' => $_REQUEST ['name'],'type' => $_REQUEST ['type'],'status' => $_REQUEST ['status'],'description' => $_REQUEST ['description'] ) );
	if ($request->error) app_error ( "Error adding product: " . $request->error, 'error', __FILE__, __LINE__ );
	$response = new \HTTP\Response ();
	$response->success = 1;
	$response->request = $request;

	print formatOutput ( $response );
}

/**
 * Get Specified Request
 */
function getRequest() {
	$request = new SupportRequest ();
	$request->get ( $_REQUEST ['code'] );

	if ($request->error) error ( "Error getting request: " . $request->error );
	$response = new \HTTP\Response ();
	$response->success = 1;
	$response->request = $request;

	print formatOutput ( $response );
}

/**
 * Find matching Requests
 */
function findRequests() {
	$requestlist = new \Support\RequestList ();

	$parameters = array ();
	if ($_REQUEST ['status']) $parameters ['status'] = $_REQUEST ['status'];

	$requests = $requestlist->find ( $parameters );
	if ($requestlist->error) app_error ( "Error finding requests: " . $requestlist->error );

	$response = new \HTTP\Response ();
	$response->success = 1;
	$response->request = $requests;

	print formatOutput ( $response );
}

/**
 * Manage Support Schema
 */
function schemaVersion() {
	$schema = new \Support\Schema ();
	if ($schema->error) {
		app_error ( "Error getting version: " . $schema->error, __FILE__, __LINE__ );
	}
	$version = $schema->version ();
	$response = new \HTTP\Response ();
	$response->success = 1;
	$response->version = $version;
	print formatOutput ( $response );
}
function schemaUpgrade() {
	$schema = new \Support\Schema ();
	if ($schema->error) {
		app_error ( "Error getting version: " . $schema->error, __FILE__, __LINE__ );
	}
	$version = $schema->upgrade ();
	$response = new \HTTP\Response ();
	$response->success = 1;
	$response->version = $version;
	print formatOutput ( $response );
}

/**
 * System Time
 *
 * @return string
 */
function system_time() {
	return date ( "Y-m-d H:i:s" );
}

/**
 * Return Properly Formatted Error Message
 */
function error($message) {
	$_REQUEST ["stylesheet"] = '';
	error_log ( $message );
	$response = new \HTTP\Response ();
	$response->message = $message;
	$response->success = 0;
	print formatOutput ( $response );
	exit ();
}

/**
 * get provinces for country in question 
 */
function getProvinces() {
	$provinceList = new \Geography\ProvinceList();
	$provicesLocated = $provinceList->find(array('country_id' => $_REQUEST ['country_id']));
	print json_encode($provicesLocated);
}

/**
 * Application Error
 *
 * @param string $message
 * @param string $file
 * @param number $line
 */
function app_error($message, $file = __FILE__, $line = __LINE__) {
	app_log ( $message, 'error', $file, $line );
	error ( 'Application Error' );
}

/**
 * Convert Object to XML
 *
 * @param mixed $object
 * @return string
 */
function formatOutput($object) {
	if (isset ( $_REQUEST ['_format'] ) && $_REQUEST ['_format'] == 'json') {
		$format = 'json';
		header ( 'Content-Type: application/json' );
	} else {
		$format = 'xml';
		header ( 'Content-Type: application/xml' );
	}
	$document = new \Document ( $format );
	$document->prepare ( $object );
	return $document->content ();
}