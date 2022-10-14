<?php
	namespace HTTP;
	    
    /**
     * Copyright (C) Phppot
     *
     * User session based token is generated and hashed with their IP address.
     * There are types of operations using which the DDL are executed.
     * Submits using general HTML form and submits using AJAX.
     * We are inserting a CSRF token inside the form and it is validated against the token present in the session.
     * This ensures that the CSRF attacks are prevented.
     *
     * If you are customizing the application and creating a new form,
     * you should ensure that the CSRF prevention is in place. form-footer.php
     * is the file that should be included where the token is to be echoed.
     * After echo the validation of the token happens in controller and it is
     * the common entry point for all calls. So there is no need to do any separate code for
     * CSRF validation with respect to each functionality.
     *
     * The CSRF token is written as a hidden input type inside the html form tag with a label $formTokenLabel.
     *
     * @author Vincy, Kevin Hinds (w/Porkchop CMS)
     */
    class CSRFService {

        protected $formTokenLabel = 'spectros-csrf-token-label';
        private $sessionTokenLabel = 'SPECTROS_CSRF_TOKEN_SESS_IDX';
        private $post = [];
        private $session = [];
        private $server = [];
        private $excludeUrl = [];
        private $hashAlgo = 'sha256';
        private $hmac_ip = true;
        private $hmacData = 'SCeNBHVyys7JSe3umAqAkF2gpvU2KLC';

        /**
         * Construct Service w/known server HTTP request and session present
         *
         * @param array $post
         * @param array $session
         * @param array $server
         * @param array $excludeUrl
         * @throws \Exception
         */
        public function __construct(&$post = null, &$session = null, &$server = null, $excludeUrl = null) {
        
            if (! \is_null($excludeUrl)) $this->excludeUrl = $excludeUrl;

            if (! \is_null($post)) {
                $this->post = & $post;
            } else {
                $this->post = & $_POST;
            }

            if (! \is_null($server)) {
                $this->server = & $server;
            } else {
                $this->server = & $_SERVER;
            }

            if (! \is_null($session)) {
                $this->session = & $session;
            } elseif (! \is_null($_SESSION) && isset($_SESSION)) {
                $this->session = & $_SESSION;
            } else {
                throw new \Exception('No session available for persistence');
            }
        }
    
        /**
         * Insert a CSRF token to a form
         *
         * @return string
         */
        public function insertHiddenField() {
            $csrfToken = $this->getCSRFToken();
            echo "<input type=\"hidden\"" . " name=\"" . $this->xssafe($this->formTokenLabel) . "\"" . " value=\"" . $this->xssafe($csrfToken) . "\"" . " />\n";
        }

        /**
         * XSS mitigation functions
         *
         * @param string $data
         * @param string $encoding
         * @return string
         */
        public function xssafe($data, $encoding = 'UTF-8') {
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML401, $encoding);
        }

        /**
         * Generate, store, and return the CSRF token
         *
         * @return string $token
         */
        public function getCSRFToken() {
            if (empty($this->session->get($this->sessionTokenLabel))) $this->session->set($this->sessionTokenLabel, bin2hex(openssl_random_pseudo_bytes(32)));
            if ($this->hmac_ip !== false) {
                $token = $this->hMacWithIp($this->session->get($this->sessionTokenLabel));
            } else {
                $token = $this->session->get($this->sessionTokenLabel);
            }
            return $token;
        }

        /**
         * hashing with IP Address removed for GDPR compliance easiness and hmacdata is used
         *
         * @param string $token
         * @return string hashed data
         */
        private function hMacWithIp($token) {
            $hashHmac = \hash_hmac($this->hashAlgo, $this->hmacData, $token);
            return $hashHmac;
        }

        /**
         * returns the current request URL
         *
         * @return string
         */
        private function getCurrentRequestUrl() {
            $protocol = "http";
            if (isset($this->server['HTTPS'])) $protocol = "https";
            $currentUrl = $protocol . "://" . $this->server['HTTP_HOST'] . $this->server['REQUEST_URI'];
            return $currentUrl;
        }

        /**
         * core function that validates for the CSRF attempt.
         *
         * @throws \Exception
         */
        public function validate() {
            $currentUrl = $this->getCurrentRequestUrl();
            if (! in_array($currentUrl, $this->excludeUrl)) {
                if (! empty($this->post)) {
                    $isAntiCSRF = $this->validateRequest();
                    
                    // CSRF attack attempt, CSRF attempt is detected. Need not reveal that information to the attacker, so just failing without info.         
                    if (! $isAntiCSRF) return false;
                    return true;
                }
            }
        }

        /**
         * the actual validation of CSRF happens here and returns boolean
         *
         * @return boolean
         */
        public function isValidRequest() {
            $isValid = false;
            $currentUrl = $this->getCurrentRequestUrl();
            if (! in_array($currentUrl, $this->excludeUrl)) {
                if (! empty($this->post)) $isValid = $this->validateRequest();
            }
            return $isValid;
        }

        /**
         * Validate a request based on session
         *
         * @return bool
         */
        public function validateRequest() {
        
            // CSRF Token not found
            if (empty($this->session->get($this->sessionTokenLabel))) return false;

            // Let's pull the POST data
            if (! empty($this->post[$this->formTokenLabel])) {    
                $token = $this->post[$this->formTokenLabel];
            } else {
                print "WTF1";
                
                 print_r($this->post);
                print_r($this->post[$this->formTokenLabel]);
                
                return false;
            }
        
            if (! \is_string($token)) return false;

            // Grab the stored token
            if ($this->hmac_ip !== false) {
                $expected = $this->hMacWithIp($this->session->get($this->sessionTokenLabel));
            } else {
                $expected = $this->session->get($this->sessionTokenLabel);
            }
            return \hash_equals($token, $expected);
        }

        /**
         * removes the token from the session
         */
        public function unsetToken() {
            if (! empty($this->session->get($this->sessionTokenLabel))) $this->session->delete($this->sessionTokenLabel);
        }
        
        /**
         * Send client to an unauthorized enpoint
         *
         * @param string $location 
         *           custom URL if needed
         */
        public function redirectUnauthorized($location = '/_register/permission_denied') {        
            header ( 'location: ' . $location);
            exit ();
        }   
    }
