<?php
	namespace Register\AuthenticationService;

	class OAuth2 Extends Base {
		private $app_id;
		private $app_secret;
		private $redirect_uri = "/_register/oauth_callback";
		private $scopes;
		private $authority;
		private $authorize_endpoint = "/oauth2/v2.0/authorize";
		private $token_endpoint = "/oauth2/v2.0/token";
		public $authState;

		public function authClient() {
			return new \Microsoft\Model\GenericProvider([
				'clientId'					=> $this->app_id,
				'clientSecret'				=> $this->app_secret,
				'redirectUri'				=> $this->redirect_uri,
				'urlAuthorize'				=> $this->authority.$this->authorize_endpoint,
				'urlAccessToken'			=> $this->authority.$this->token_endpoint,
				'urlResourceOwnerDetails'	=> '',
				'scopes'					=> $this->scopes
			]);
		}
		public function authenticate($login,$password) {
			if (! $login) {
				app_log("No 'login' for authentication");
				return false;
			}

			$oAuthClient = $this->authClient();

			$authUrl = $oAuthClient->getAuthorizationUrl();
			$authState = $oAuthClient->getState();
			return true;
		}

		public function callBack() {
			$expectedState = $authState;
			unset($authState);

			if (!isset($_GET['state']) || !isset($_GET['code'])) {
				$this->error("No OAuth Session");
				return false;
			}

			$providedState = $_GET['state'];

			if (!isset($expectedState)) {
				$this->error("Expected State Not Available");
				return false;
			}

			if (!isset($providedState) || $expectedState != $providedState) {
				$this->error("State Does Not Match");
				return false;
			}

			$authCode = $_GET['code'];
			if (isset($authCode)) {
				$oAuthClient = new GenericProvider([
					'clientId'					=> $this->app_id,
					'clientSecret'				=> $this->app_secret,
					'redirectUri'				=> $this->redirect_uri,
					'urlAuthorize'				=> $this->authority.$this->authorize_endpoint,
					'urlAccessToken'			=> $this->authority.$this->token_endpoint,
					'urlResourceOwnerDetails'	=> '',
					'scopes'					=> $this->scopes
				]);
			}

			$accessToken = null;
			try {
				// Make the token request
				$accessToken = $oAuthClient->getAccessToken('authorization_code', [ 'code' => $authCode ]);
			}
			catch (\League\OAuth2\Client\Provider\Exception\IdentityPrividerException $e) {
				$this->error(urlencode($e->getMessage()));
				return false;
			}

			$user = [];
			if (null !== $accessToken) {
				$graph = new Graph();
				$graph->setAccessToken($accessToken->getToken());
				try {
					$azureUser = $graph->createRequest('GET','/me?$select=displayName,mail,userPrincipalName')
						->setReturnType(Model\User::class);
			
				}
				catch (Exception $exception) {
					$this->error('Unable to get user details: '.$exception->getMessage());
					return false;
				}

				$user = [
					'name'	=> $azureUser->getDisplayName(),
					'email'	=> $azureUser->getMail()
				];
			}
			return true;
		}

		public function changePassword($login,$password) {
			app_log($GLOBALS['_SESSION_']->customer->login." changing password for ".$this->login,'info');

			$this->error("OAuth2 password change not supported");	
			return false;
		}
	}
