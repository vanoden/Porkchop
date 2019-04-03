<?php
	namespace Captcha;

	class Captcha {

        /**
         * check the user filled out the correct captcha
         *
         * @param string $gRecaptchaResponse
         */
        public function verifyRecaptcha($gRecaptchaResponse = '') {
	        
            // get any known IP address		
	        $ipAddress = $this->getClientIP();
	        
	        // if no Google response for the captcha, then it's not valid
	        if (!$gRecaptchaResponse || empty($gRecaptchaResponse) || $gRecaptchaResponse == 'null') {
		        logger::logError ( 'Bad Captcha Submission, Empty gRecaptchaResponse: ' . $gRecaptchaResponse . ' Remote IP Address: ' . print_r ( $ipAddress, true ) );
	            echo 'bad captcha';
	            exit ();
	        }
	        
	        // verify captcha, swap out the secret key check for the new testing "invisible" captcha
	        $checkCaptchaURL = "https://www.google.com/recaptcha/api/siteverify?secret=6LfrepcUAAAAAAvapiGvscdl9bs-dir1jpFzITn_&response=" . $gRecaptchaResponse . "&remoteip=" . $ipAddress;        
	        
	        // issue a regular ass cURL here call here [other HTTP code libraries / plugins can cause strange issues and Google blocks you]
	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
	        
	        // required for https urls       
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	
	        
	        // request via proxy if QA or PROD
	        if (!empty(PROXY_PORT) && !empty(PROXY_HOST) && ENVIRONMENT!='development') {
	            curl_setopt($ch, CURLOPT_PROXY, PROXY_HOST);
	            curl_setopt($ch, CURLOPT_PROXYPORT, PROXY_PORT);
	        }
	        
	        curl_setopt($ch, CURLOPT_URL, $checkCaptchaURL);
	        $response = json_decode(curl_exec($ch));
	        curl_close($ch);
	        
	        // if captcha is bad, then say so and log the issue
	        if ($response->success == false) {
		        logger::logError ( 'Bad Captcha Submission, Recaptcha URL: ' . $checkCaptchaURL . ' Response: ' . print_r ( $response, true ) . ' Remote IP Address: ' . print_r ( $ipAddress, true ) );
		        echo 'bad captcha';
	            exit ();
	        }
	        return true;
        }

        /**
         * get the client IP address
         * via sanitized HAProxy HTTP_X_FORWARDED_FOR if possible
         *
         * @return Ambigous <string, integer>
         */
        public function getClientIP() {
	        $ipaddress = false;
	        if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
		        $ipaddress = $_SERVER ['HTTP_X_FORWARDED_FOR'];
	        } elseif (isset ( $_SERVER ['HTTP_X_FORWARDED'] )) {
		        $ipaddress = $_SERVER ['HTTP_X_FORWARDED'];
	        } elseif (isset ( $_SERVER ['HTTP_FORWARDED_FOR'] )) {
		        $ipaddress = $_SERVER ['HTTP_FORWARDED_FOR'];
	        } elseif (isset ( $_SERVER ['HTTP_FORWARDED'] )) {
		        $ipaddress = $_SERVER ['HTTP_FORWARDED'];
	        } elseif (isset ( $_SERVER ['REMOTE_ADDR'] )) {
		        $ipaddress = $_SERVER ['REMOTE_ADDR'];
	        } elseif (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
		        $ipaddress = $_SERVER ['HTTP_CLIENT_IP'];
	        }
	        
	        // if comma separated value from load balancing, the grab the first entry
	        if (strpos ( $ipaddress, ',' ) !== false) {
		        $ipaddressInfo = preg_split ( '/,/', $ipaddress );
		        if (is_array ( $ipaddressInfo )) $ipaddress = array_shift ( $ipaddressInfo );
	        }
	        return $ipaddress;
        }
	}
