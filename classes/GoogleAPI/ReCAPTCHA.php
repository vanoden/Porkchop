<?php
	namespace GoogleAPI;

	class ReCAPTCHA Extends \BaseClass {	
		public function test($customer,$response) {
			$ok = $this->verifyToken($response);
			$status = (is_object($customer) && isset($customer->status)) ? $customer->status : '';
			if ($ok && ! $this->bypassed()) {
				app_log("ReCAPTCHA presented and SOLVED for " . $status . " Customer (must be a human attempting)" , 'notice' , __FILE__ , __LINE__);
			}
			elseif (! $ok) {
				app_log("ReCAPTCHA presented and FAILED for " . $status . " Customer" , 'notice' , __FILE__ , __LINE__);
			}
			return $ok;
		}

		/** Verify a reCAPTCHA response token. Does not require a customer record. */
		public function verifyToken($response): bool {
			if ($this->bypassed()) {
				app_log("Bypassed CAPTCHA!",'notice');
				return true;
			}

			$response = trim((string)$response);
			if ($response === '') {
				$this->error("Please complete the CAPTCHA.");
				return false;
			}
			if (empty($GLOBALS['_config']->captcha->private_key)) {
				$this->error("CAPTCHA is not configured.");
				return false;
			}

			$url = "https://www.google.com/recaptcha/api/siteverify";
			$data = array(
				'secret'	=> $GLOBALS['_config']->captcha->private_key,
				'response'	=> $response,
				'remoteip'	=> $_SERVER['REMOTE_ADDR'] ?? '',
			);

			$options = array(
				'http'	=> array(
					'method'	=> 'POST',
					'content'	=> http_build_query($data),
				),
			);

			unset($_REQUEST['g-recaptcha-response']);
			unset($_REQUEST['btn_submit']);

			$context = stream_context_create($options);
			$result = @file_get_contents($url,false,$context);
			$captcha_success = is_string($result) ? json_decode($result) : null;

			if (is_object($captcha_success) && ! empty($captcha_success->success)) {
				return true;
			}

			$this->error("Sorry, CAPTCHA Invalid.  Please Try Again");
			return false;
		}

		private function bypassed(): bool {
			return isset($GLOBALS['_config']->captcha->bypass_key)
				&& isset($_REQUEST['captcha_bypass_key'])
				&& ($GLOBALS['_config']->captcha->bypass_key == $_REQUEST['captcha_bypass_key']);
		}
	}
