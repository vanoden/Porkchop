<?php
	namespace Email\Transport;

	class Proxy Extends Base {
		/** @method protected _deliver(email)
		 * Sends the email using the proxy transport.
		 * @param \Email\Message $email The email message to send.
		 * @return bool Returns true on success, false on failure.
		 */
		protected function _deliver($email) {
			// Build the request to send the email
			$request = new \HTTP\Request();
			$request->url('http://'.$this->hostname().'/send.php');
			if ($request->error()) {
				$this->error($request->error());
				return false;
			}

			$request->method('POST');
			$request->addParam('token',$this->token());
			$request->addParam('to',$email->to());
			$request->addParam('from',$email->from());
			$request->addParam('subject',$email->subject());
			$request->addParam('body',urlencode($email->body()));
			if ($email->html()) $request->addParam('html','true');
			app_log("Email request: '".$request->serialize()."'",'trace');

			// Connect to the proxy server and send the request
			$client = new \HTTP\Client();
			$client->connect($this->hostname());
			if ($client->error()) {
				$this->error("Cannot connect to host: ".$client->error());
				return false;
			}
			$response = $client->post($request);
			if ($client->error()) {
				$this->error("Cannot send request: ".$client->error());
				return false;
			}
			app_log("Email response: ".print_r($response,true),'trace');
			if ($response->code() == 200) {
				$content = $response->content();
				app_log($content);
				if (preg_match('/^ERROR\:\s(.*)$/',$content,$matches)) {
					$this->error($matches[1]);
					$this->_result = "Failed";
					return false;
				}
				elseif (preg_match('/Mailer\sError/',$content)) {
					$this->error($content);
					$this->_result = "Failed";
					return false;
				}
				$this->_result = "Sent";
				return true;
			}
			else {
				$this->_result = "Failed";
				$this->error($response->code().": ".$response->status());
				return false;
			}
		}
	}
