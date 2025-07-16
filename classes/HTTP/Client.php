<?php
	namespace HTTP;

use function Amp\now;

	class Client Extends \BaseClass {
		private $_socket;
		private $_connected = false;
		private $_response;
		private $_cookiejar;
		private $_timeout = 3; // Default timeout in seconds
		private $_status = 'UNKNOWN';

		/** @constructor
		 * Initializes the HTTP client
		 * Creates a new HTTP client instance with an empty cookie jar.
		 */
		public function __construct() {
			$this->_cookiejar = new \HTTP\CookieJar();
		}

		/** @method connect(string $host, int $port)
		 * Connects to the specified host and port
		 * @param string $host The hostname or IP address to connect to
		 * @param int $port The port number to connect to (default is 80)
		 * @return bool Returns true on success, false on failure
		 */
		public function connect($host = '127.0.0.1',$port = null, $ssl = false) {
			if (preg_match('/^(https?):\/\/([\w\-\.]+)\:(\d+)/',$host,$matches)) {
				$protocol = $matches[1];
				$host = $matches[2];
				$port = $matches[3];
				if ($protocol == 'https') $ssl = true;
				else $ssl = false;
				if (!$port) {
					if ($ssl) $port = 443;
					else $port = 80;
				}
			}
			elseif (preg_match('/(https?):\/\/([\w\-\.]+)\/?/',$host,$matches)) {
				$protocol = $matches[1];
				$host = $matches[2];
				if ($protocol == 'https') $ssl = true;
				else $ssl = false;
				if (!$port) {
					if ($ssl) $port = 443;
					else $port = 80;
				}
			}
			else {
				if (!$ssl) {
					if ($port == 443) $ssl = true;
				}
			}

			if (empty($port)) {
				if ($ssl) $port = 443;	 // Default port for HTTPS
				else $port = 80; 		// Default port for HTTP
			}

			if ($ssl) $service = "ssl://".$host.":".$port;
			else $service = "tcp://".$host.":".$port;
			$this->_socket = stream_socket_client($service, $errno, $errstr, $this->_timeout);

			if (!$this->_socket) {
				$this->error("Unable to connect to host: ".$errstr);
				return false;
			}
			$this->_status = 'CONNECTED';
			$this->_connected = true;
			return true;
		}

		/** @method post(HTTP\Request $request)
		 * Sends an HTTP POST request
		 * @param HTTP\Request $request The request to send
		 * @return HTTP\Response|null The response object or null on failure
		*/
		public function post($request) {
			$request->method('POST');
			return $this->request($request);
		}
	
		/** @method get(HTTP\Request $request)
		 * Sends an HTTP GET request
		 * @param HTTP\Request $request The request to send
		 * @return HTTP\Response|null The response object or null on failure
		*/
		public function get($request): Response|null {
			$request->method('GET');
			return $this->request($request);
		}

		/** @method request(HTTP\Request $request)
		 * Sends an HTTP request
		 * @param HTTP\Request $request The request to send
		 * @return HTTP\Response|null The response object or null on failure
		*/
		public function request($request): Response|null {
			if (! $this->_connected) {
				$this->error("Not connected");
				return null;
			}

			$string = $request->serialize();
			if ($request->error()) {
				$this->error("Error preparing request: ".$request->error());
				return null;
			}

			$start_time = time();
	
			// Send Request to the Server
			$this->_status = 'SENDING';
			fwrite($this->_socket,$string);
			app_log("Message sent at ".(time() - $start_time)." seconds",'trace');

			/** @section Collect Response
			 * Listen for and store bytes returned from the server
			 */
			$content = "";
			$body = "";
			$content_length = 0;
			$this->_status = 'RECEIVING';
			$content = fread($this->_socket, 1);
			app_log("First byte received at ".(time() - $start_time)." seconds",'trace');
			while ($buffer = fread($this->_socket, 2048)) {
				$content .= $buffer;
				if ($this->_status == 'RECEIVING' && strpos($content, "\r\n\r\n") !== false) {
					app_log("Headers received at ".(time() - $start_time)." seconds",'trace');
					// We have received the headers, stop reading
					if (preg_match('/Content\-Length:\s*(\d+)/i', $content, $matches)) {
						$content_length = (int)$matches[1];
						$this->_status = 'BODY';
						$body = substr($content, strpos($content, "\r\n\r\n") + 4);
					}
				}
				if ($this->_status == 'BODY') {
					// Grab the rest of the body
					// If we have already received some body data
					if ($content_length - strlen($body) <= 0) {
						app_log("Receiving remaining ".$content_length." bytes of body data at ".(time() - $start_time)." seconds",'trace');
						$buffer = fread($this->_socket, $content_length - strlen($body));
						$content .= $buffer;
						app_log("Received ".strlen($buffer)." bytes of body data at ".(time() - $start_time)." seconds",'trace');
					}
					// We are still receiving the body
					if (strlen($content) >= $content_length + strlen($body)) {
						// We have received the full body, stop reading
						app_log("Got ".$content_length." bytes of body data at ".(time() - $start_time)." seconds",'trace');
						app_log("Full body received at ".(time() - $start_time)." seconds",'trace');
						break;
					}
				}
			}
			fclose($this->_socket);
			if (strlen($content) < 1) {
				$this->error("No response received");
				return null;
			}
			else {
				app_log("Received: ".strlen($content)." char");
			}

			/** @section Parse Response
			 * Parse the returned chars as an HTTP Response
			 */
			$this->_response = new \HTTP\Response();

			if ($this->_response->parse($content)) {
				# Store Cookies
				$this->_cookiejar->add($this->_response->cookies());
				return $this->_response;
			}
			else {
				$this->error($this->_response->error());
				return null;
			}
		}

		/** @method cookies()
		 * Returns all cookies stored in the cookie jar
		 * @return array An array of cookies
		 */
		public function cookies() {
			return $this->_cookiejar->all();
		}
	}
