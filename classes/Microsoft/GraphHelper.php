<?php
namespace Microsoft;

/*
	Modified from Microsoft Graph https://docs.microsoft.com/en-us/graph/tutorials/php?tabs=aad
	A. Caravello 7/28/2022
*/

require THIRD_PARTY.'/autoload.php';

class GraphHelper {
	private static \GuzzleHttp\Client $tokenClient;
	private static string $clientId;
	private static string $authTenant;
	private static string $graphUserScopes;
	private static \Microsoft\Graph\Graph $userClient;
	private static string $userToken;

	public function __construct() {
	}
	public static function initializeGraphForUserAuth(): void {
		GraphHelper::$tokenClient = new \GuzzleHttp\Client();
		GraphHelper::$userClient = new \Microsoft\Graph\Graph();
	}

	public static function getUserToken(): string {
		// If we already have a user token, just return it
		// Tokens are valid for one hour, after that it needs to be refreshed
		if (isset(GraphHelper::$userToken)) {
			return GraphHelper::$userToken;
		}

		// https://docs.microsoft.com/azure/active-directory/develop/v2-oauth2-device-code
		$deviceCodeRequestUrl = 'https://login.microsoftonline.com/'.$GLOBALS['_config']->OAuth2->authTenant.'/oauth2/v2.0/devicecode';
		$tokenRequestUrl = 'https://login.microsoftonline.com/'.$GLOBALS['_config']->OAuth2->authTenant.'/oauth2/v2.0/token';

		// First POST to /devicecode
		$deviceCodeResponse = json_decode(GraphHelper::$tokenClient->post($deviceCodeRequestUrl, [
			'form_params' => [
				'client_id'	=> $GLOBALS['_config']->OAuth2->clientId,
				'scope'		=> $GLOBALS['_config']->OAuth2->graphUserScopes
			]
		])->getBody()->getContents());

		// Display the user prompt
		print($deviceCodeResponse->message.PHP_EOL);

		// Response also indicates how often to poll for completion
		// And gives a device code to send in the polling requests
		$interval = (int)$deviceCodeResponse->interval;
		$device_code = $deviceCodeResponse->device_code;

		// Do polling - if attempt times out the token endpoint
		// returns an error
		while (true) {
			sleep($interval);

			// POST to the /token endpoint
			$tokenResponse = GraphHelper::$tokenClient->post($tokenRequestUrl, [
				'form_params' => [
					'client_id' => $GLOBALS['_config']->OAuth2->clientId,
					'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
					'device_code' => $device_code
				],
				// These options are needed to enable getting
				// the response body from a 4xx response
				'http_errors' => false,
				'curl' => [
					CURLOPT_FAILONERROR => false
				]
			]);

			if ($tokenResponse->getStatusCode() == 200) {
				// Return the access_token
				$responseBody = json_decode($tokenResponse->getBody()->getContents());
				GraphHelper::$userToken = $responseBody->access_token;
				return $responseBody->access_token;
			} else if ($tokenResponse->getStatusCode() == 400) {
				// Check the error in the response body
				$responseBody = json_decode($tokenResponse->getBody()->getContents());
				if (isset($responseBody->error)) {
					$error = $responseBody->error;
					// authorization_pending means we should keep polling
					if (strcmp($error, 'authorization_pending') != 0) {
						throw new \Exception('Token endpoint returned '.$error, 100);
					}
				}
			}
		}
	}

	public static function getUser(): \Microsoft\Graph\Model\User {
		$token = GraphHelper::getUserToken();
		GraphHelper::$userClient->setAccessToken($token);
	
		return GraphHelper::$userClient->createRequest('GET', '/me?$select=displayName,mail,userPrincipalName')
									   ->setReturnType(\Microsoft\Graph\Model\User::class)
									   ->execute();
	}

	public static function getUserGroups() {
		$token = GraphHelper::getUserToken();
		GraphHelper::$userClient->setAccessToken($token);

		return GraphHelper::$userClient->createRequest('GET', '/me/transitiveMemberOf/microsoft.graph.group?$count=true')
									   ->execute();
	}
}
?>
