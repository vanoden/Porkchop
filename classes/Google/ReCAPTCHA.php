<?php
	namespace Google;

	class ReCAPTCHA Extends \BaseClass {
		private $url = "https://www.google.com/recaptcha/api/siteverify";

		public function verify($response) {
			$data = array(
				'secret'	=> $GLOBALS['_config']->captcha->private_key,
				'response'	=> $_REQUEST['g-recaptcha-response'],
				'remoteip'	=> $_SERVER['REMOTE_ADDR'],
			);

			$options = array(
				'http'  => array(
					'method'	=> 'POST',
					'content'	=> http_build_query($data),
				),
			);
  
			$context = stream_context_create($options);
			$result = file_get_contents($this->url,false,$context);
			$captcha_success = json_decode($result);
		}
	}
