<?php
	$page = new \Site\Page;

	$site = new \Site;
	require THIRD_PARTY.'/autoload.php';

	const OAUTH_AUTHORITY          = 'https://login.microsoftonline.com/common';
	const OAUTH_AUTHORIZE_ENDPOINT = '/oauth2/v2.0/authorize';
	const OAUTH_TOKEN_ENDPOINT     = '/oauth2/v2.0/token';

	$title = 'Hello public world!';

	// Simple PHP routing
	$path = '/_register/oauth';
	$user = null;
	$host = $site->url();

	// Checking for user
	$user = [];
	if (isset($_SESSION['user'])) {
		$user = unserialize($_SESSION['user']);
		$title = 'Hello private world';
	}

	// Checking for messages
	$style = 'success';
	$displayMessage = '';
	if (isset($_GET['type']) && isset($_GET['message'])) {
		$styles = ['success', 'error'];
		if (in_array($_GET['type'], $styles)) {
			$style = $_GET['type'];
		}
		$displayMessage = $_GET['message'];
	}

	if (preg_match('/^(LOGIN|LOGOUT|CALLBACK)$/',$_REQUEST['method'],$matches)) {
		$method = $matches[1];
	}
	else {
		$method = '';
	}

	if ($method == 'LOGOUT') {
		$GLOBALS['_SESSION_']->end();
		header('Location: /');
	}

	if ($method == 'LOGIN') {
		$oAuthClient = new \League\OAuth2\Client\Provider\GenericProvider([
			'clientId'                => $GLOBALS['_config']->OAuth2->Graph->clientId,
			'clientSecret'            => $GLOBALS['_config']->OAuth2->Graph->clientSecret,
			'redirectUri'             => $host.$GLOBALS['_config']->OAuth2->Graph->redirectUri,
			'urlAuthorize'            => OAUTH_AUTHORITY . OAUTH_AUTHORIZE_ENDPOINT,
			'urlAccessToken'          => OAUTH_AUTHORITY . OAUTH_TOKEN_ENDPOINT,
			'urlResourceOwnerDetails' => '',
			'scopes'                  => $GLOBALS['_config']->OAuth2->Graph->graphUserScopes
		]);

		$authUrl = $oAuthClient->getAuthorizationUrl();
		$GLOBALS['_SESSION_']->oauthState($oAuthClient->getState());
		header('Location: ' . $authUrl);
	}

	if ($method == 'CALLBACK') {
		$expectedState = $_SESSION['oauthState'];
		unset($_SESSION['oauthState']);

		if (!isset($_GET['state']) || !isset($_GET['code'])) {
			header('Location: ' . $host . '/?type=error&message=No%20OAuth%20session');
		}

		$providedState = $_GET['state'];

		if (!isset($expectedState)) {
		// If there is no expected state in the session,
		// do nothing and redirect to the home page.
		header('Location: ' . $host . '/?type=error&message=Expected%20state%20not%20available');
		}

		if (!isset($providedState) || $expectedState != $providedState) {
		header('Location: ' . $host . '/?type=error&message=State%20does%20not%20match');
		}

		// Authorization code should be in the "code" query param
		$authCode = $_GET['code'];
		if (isset($authCode)) {
			// Initialize the OAuth client
			$oAuthClient = new \League\OAuth2\Client\Provider\GenericProvider([
				'clientId'                => $GLOBALS['_config']->OAuth2->Graph->clientId,
				'clientSecret'            => $GLOBALS['_config']->OAuth2->Graph->clientSecret,
				'redirectUri'             => $host.$GLOBALS['_config']->OAuth2->Graph->redirectUri,
				'urlAuthorize'            => OAUTH_AUTHORITY . OAUTH_AUTHORIZE_ENDPOINT,
				'urlAccessToken'          => OAUTH_AUTHORITY . OAUTH_TOKEN_ENDPOINT,
				'urlResourceOwnerDetails' => '',
				'scopes'                  => $GLOBALS['_config']->OAuth2->Graph->graphUserScopes
			]);

			$accessToken = null;
			try {
				// Make the token request
				$accessToken = $oAuthClient->getAccessToken('authorization_code', [
				'code' => $authCode
				]);
			} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
				header('Location: ' . $host . '/?type=error&message=' . urlencode($e->getMessage()));
			}
		}

		$user = [];
		if (null !== $accessToken) {
			$graph = new \Microsoft\Graph\Graph();
			$graph->setAccessToken($accessToken->getToken());
			try {
				$azureUser = $graph->createRequest('GET', '/me?$select=displayName,mail,userPrincipalName')
					->setReturnType(Model\User::class)
					->execute();
			} catch (Exception $exception) {
				header('Location: ' . $host . '/?type=error&message=' . urlencode('Unable to get user details: ' . $exception->getMessage()));
			}

			$user = [
				'name' => $azureUser->getDisplayName(),
				'email' => $azureUser->getMail(),
			];
			$_SESSION['user'] = serialize($user);
		}
		header('Location: ' . $host);
	}
?>
<!DOCTYPE html>
<html lang="en_US">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlentities($title, ENT_QUOTES, 'UTF-8') ?></title>
        <style type="text/css">
            html {
                font-family: Helvetica, Arial, sans-serif;
            }
            .error, .success {
                padding: 5px 15px;
            }
            .error {
                background-color: lightpink;
                border: 1px solid darkred;
                color: darkred;
            }
            .success {
                background-color: lightgreen;
                border: 1px solid darkgreen;
                color: darkgreen;
            }
        </style>
    </head>
    <body>
        <h1><?php echo htmlentities($title, ENT_QUOTES, 'UTF-8') ?></h1>
        <p>Welcome to PHP <strong><?php echo phpversion() ?></strong> on Azure App Service <strong><?php echo gethostname() ?></strong>.</p>
        <p>
            <a href="/">Home</a>
            <a href="/_register/oauth?method=LOGIN">Login</a>
            <a href="/_register/oauth?method=LOGOUT">Logout</a>
        </p>
        <?php if ('' !== $displayMessage): ?>
        <div class="<?php echo $style ?>">
            <p><?php echo htmlentities($displayMessage, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <?php endif ?>
        <?php if ([] !== $user): ?>
            <p>User details</p>
            <ul>
                <li><strong>Name:</strong> <?php echo htmlentities($user['name'], ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>Email:</strong> <?php echo htmlentities($user['email'], ENT_QUOTES, 'UTF-8') ?></li>
            </ul>
        <?php endif ?>
    </body>
</html>