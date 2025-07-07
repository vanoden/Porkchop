<?php

namespace Slack;

class Client Extends \BaseClass {
    // Client implementation
	private $token;			// Slack API Bot Token

	/** @constructor
	 * Set the token from the Site Config
	 */
	public function __construct() {
		$this->token = $GLOBALS['_config']->slack->bot_token;
	}

	/** @method send(string $channel, string $message)
	 * Sends a message to a Slack channel
	 * @param string $channel The Slack channel to send the message to
	 * @param string $message The message to send
	 * @return bool Returns true on success, throws an exception on failure
	*/
	public function send($channel, $message) {
		// Send a message to a Slack channel
		$service = 'https://slack.com';
		$uri = '/api/chat.postMessage';
		$url = $service.$uri;
		$data = [
			'channel' => $channel,
			'text' => $message
		];

		$request = new \HTTP\Request();
		$request->url($url);
		$request->addHeader('Content-Type', 'application/json; charset=utf-8');
		$request->addHeader('Authorization', 'Bearer ' . $this->token);
		$request->body(json_encode($data));

		$client = new \HTTP\Client();
		if ($client->connect($service)) {
			$response = $client->post($request);
			// Handle the response
			if ($response->error()) {
				$this->error("Error sending message: ".$response->error());
				return false;
			}
			elseif (preg_match('/application\/json/',$response->header("content-type"))) {
				$object = json_decode($response->content());
				if ($object->success == 1) {
					return true;
				}
				else {
					$this->error("Error sending message: ".$object->error);
					return false;
				}
			}
			else {
				$this->error("Unexpected response format: ".$response->header("content-type"));
				return false;
			}
		}
		else {
			$this->error("Cannot connect to host: ".$client->error());
			return false;
		}
	}

	/** @method validChannel(string)
	 * Validate the channel name
	 * @param string $channel The channel name to validate
	 * @return bool Returns true if the channel name is valid, false otherwise
	 */
	public function validChannel($channel) {
		// Validate the channel name
		if (empty($channel) || !is_string($channel)) {
			return false;
		}
		if (preg_match('/^#[A-Za-z0-9_-]+$/', $channel) !== 1) {
			return false;
		}
		return true;
	}

	/** @method validMessage(string)
	 * Validate the message content
	 * @param string $message The message content to validate
	 * @return bool Returns true if the message content is valid, false otherwise
	 */
	public function validMessage($message) {
		// Validate the message content
		if (empty($message) || !is_string($message)) {
			return false;
		}
		if (strlen($message) > 4000) {
			return false;
		}
		return true;
	}

	/** @method validToken($string)
	 * Validate the Slack API token
	 * @param string $token The token to validate
	 * @return bool Returns true if the token is valid, false otherwise
	 */
	public function validToken($token) {
		// Validate the token
		if (empty($token) || !is_string($token)) {
			return false;
		}
		if (strlen($token) !== 36) {
			return false;
		}
		if (!preg_match('/^[A-Za-z0-9]{24}\.[A-Za-z0-9]{6}\.[A-Za-z0-9_-]+$/', $token)) {
			return false;
		}
		return true;
	}

	/** @method validSender(string)
	 * Validate the Sender Name
	 * @param string $string
	 * @return bool Returns true if the sender is valid, false otherwise
	*/
	public function validSender($string) {
		return true;
	}
}