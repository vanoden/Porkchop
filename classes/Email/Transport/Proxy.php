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
			$request->addParam('to',preg_replace('/\+/', '%2B', $email->to()));
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
				app_log("Email transport error: ".$client->error(),'error',__FILE__,__LINE__);
				return false;
			}
			if (is_null($response)) {
				$this->error("No response received from email proxy server");
				app_log("Email transport error: No response received from proxy server",'error',__FILE__,__LINE__);
				$this->_result = "Failed";
				return false;
			}
			app_log("Email response: ".print_r($response,true),'trace');
			$responseCode = $response->code();
			if ($responseCode == 200) {
				$content = $response->content();
				app_log("Email proxy response content: ".$content,'debug',__FILE__,__LINE__);
				if (preg_match('/^ERROR\:\s(.*)$/',$content,$matches)) {
					$this->error($matches[1]);
					$this->_result = "Failed";
					app_log("Email proxy returned error: ".$matches[1],'error',__FILE__,__LINE__);
					return false;
				}
				elseif (preg_match('/Mailer\sError/',$content)) {
					$this->error($content);
					$this->_result = "Failed";
					app_log("Email proxy returned mailer error: ".$content,'error',__FILE__,__LINE__);
					return false;
				}
				// Check for explicit success indicators
				if (preg_match('/success|sent|ok/i',$content)) {
					$this->_result = "Sent";
					app_log("Email successfully sent via proxy",'info',__FILE__,__LINE__);
					return true;
				}
				// If no error patterns match but also no success pattern, log warning but assume success
				// (some proxy servers may return empty or generic 200 responses)
				app_log("Email proxy returned 200 with content that doesn't match expected patterns: ".$content,'notice',__FILE__,__LINE__);
				$this->_result = "Sent";
				return true;
			}
			else {
				$this->_result = "Failed";
				$errorMsg = $responseCode.": ".$response->status();
				$this->error($errorMsg);
				app_log("Email proxy returned error code: ".$errorMsg,'error',__FILE__,__LINE__);
				return false;
			}
		}
	}
