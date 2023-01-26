<?php
	namespace GoogleAPI;

	class ReCAPTCHA Extends \BaseClass {	
		public function test($customer,$response) {
			if (isset($GLOBALS['_config']->captcha->bypass_key) && isset($_REQUEST['captcha_bypass_key']) && ($GLOBALS['_config']->captcha->bypass_key == $_REQUEST['captcha_bypass_key'])) return true;

			# Check reCAPTCHA
			$url = "https://www.google.com/recaptcha/api/siteverify";
			$data = array(
				'secret'	=> $GLOBALS['_config']->captcha->private_key,
				'response'	=> $response,
				'remoteip'	=> $_SERVER['REMOTE_ADDR'],
			);

			$options = array(
				'http'	=> array(
					'method'	=> 'POST',
					'content'	=> http_build_query($data),
				),
			);
							
			# Don't need to store these fields
			unset($_REQUEST['g-recaptcha-response']);
			unset($_REQUEST['btn_submit']);

			$context = stream_context_create($options);
			$result = file_get_contents($url,false,$context);
			$captcha_success = json_decode($result);
			
			if ($captcha_success->success == true ) {
				app_log("ReCAPTCHA presented and SOLVED for " . $customer->status . " Customer (must be a human attempting)" , 'notice' , __FILE__ , __LINE__);
				$customer->update(array('status' => 'ACTIVE'));
				return true;
			}
			else {
				$this->error("Sorry, CAPTCHA Invalid.  Please Try Again");
				app_log("ReCAPTCHA presented and FAILED for " . $customer->status . " Customer" , 'notice' , __FILE__ , __LINE__);
				return false;
			}
		}
	}