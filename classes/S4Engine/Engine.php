<?php
	namespace S4Engine;

	/**
	 * This class represents the Envelope Containing an S4 Request/Response
	 * S4 Packet Format
	 * 1 byte - Start Terminator
	 * 2 bytes - Client ID
	 * 2 bytes - Server ID
	 * 2 bytes - Length
	 * 2 bytes - Type
	 * x bytes - Session Code
	 * 1 byte - Start of Text
	 * n bytes - Data
	 * 1 byte - End of Text
	 * 2 byte - checksum
	 * 1 byte - End Terminator
	 * 
	 * [1][0][1][9][9][0][5][0][1]...[2]...[3][1][2][4]
	 */
	class Engine Extends \BaseClass {
		protected ?\S4Engine\Session $_session = null;	// Session Object
		protected int $_serverId = 0;			// Server ID
		protected int $_clientId = 0;			// Client ID
		protected int $_typeId = 0;				// Type of Message
		protected $_message;					// Message contained in envelope
		protected $_checksum;					// 2 Byte Checksum
		protected int $_meta_chars = 18;		// Number of header, footer and delimiter chars for completion checking, 14 + $_sessionCodeLen
		protected int $_sessionCodeLen = 4;		// Length of the session code
		protected array $_sessionCode = [];	// Session Code from received envelope header

		/** 
		 * Constructor
		 */
		public function __construct($id = 0) {
			$this->_session = new \S4Engine\Session();
		}

		/**
		 * Extract and parse the binary envelope if available
		 * @param reference to buffer
		 * @return bool True if parsed successfully, else false
		*/
		public function parse(&$buffer): bool {
			if (strlen($buffer) == 0) {
				$this->error("No data to parse");
				return false;
			}

			$byteString = "";
			$headerLength = 9 + $this->_sessionCodeLen;

			for ($i = 0; $i < strlen($buffer); $i++) {
				$byteString .= "[".ord(substr($buffer,$i,1))."]";
			}

			// Check for starting terminator
			while (strlen($buffer) > 0 && (ord(substr($buffer,0,1)) != 1 || ord(substr($buffer,$headerLength,1)) != 2)) {
				app_log("Missing Start Terminator: [".ord(substr($buffer,0,1))."]","trace");
				app_log("Or Missing Start of Text: [".ord(substr($buffer,$headerLength,1))."]","trace");
				app_log("Dropping 1st character: [".ord(substr($buffer,0,1))."]","trace");
				$buffer = substr($buffer,1);
			}
			if (strlen($buffer) == 0) {
				$this->error("No data to parse");
				return false;
			}

			app_log("Got Start Terminator",'trace');

			// Check for a complete header
			if (strlen($buffer) < $headerLength) {
				$byteString = "";
				//$this->error("Not enough data yet for header, only ".strlen($buffer)." chars");
				for ($i = 0; $i < strlen($buffer); $i++) {
					$byteString .= "[".ord(substr($buffer,$i,1))."]";
				}
				app_log("Buffer: $byteString","trace");
				return false;
			}

			$contentLength = ord(substr($buffer,5,1)) * 256 + ord(substr($buffer,6,1));
			app_log("Expecting ".$contentLength." chars of data",'trace');

			app_log("Got " . strlen($buffer) . " of " . ($contentLength + $this->_meta_chars) . " bytes",'trace');

			// Check for a minimal complete header using terminators
			if (ord(substr($buffer,0,1)) == 1 && ord(substr($buffer,$headerLength,1)) == 2) {
				if (strlen($buffer) < $contentLength + $this->_meta_chars) {
					//print "Not enough data yet for body, only ".strlen($buffer)." chars\n";
					return false;
				}

				app_log("SOH: ".ord(substr($buffer,0,1)),'trace');
				app_log("SOT: ".ord(substr($buffer,$headerLength + 1,1)),'trace');
				app_log("CID: [" . ord(substr($buffer,1,1)) . "][" . ord(substr($buffer,2,1)) . "]",'trace');
				app_log("SID: [" . ord(substr($buffer,3,1)) . "][" . ord(substr($buffer,4,1)) . "]",'trace');
				$this->_clientId = ord(substr($buffer,1,1)) * 256 + ord(substr($buffer,2,1));
				$this->_typeId = ord(substr($buffer,7,1)) * 256 + ord(substr($buffer,8,1));
				$this->_sessionCode = [];
				for ($i = 0; $i < $this->_sessionCodeLen; $i++) {
					array_push($this->_sessionCode,ord(substr($buffer,$i + 9,1)));
				}
				$client = new \S4Engine\Client();
				$client->codeString(chr(floor($this->_clientId / 256)).chr(floor($this->_clientId % 256)));
				$session = new \S4Engine\Session();
				$session->client($client);

				$serverIdIn = ord(substr($buffer,3,1)) * 256 + ord(substr($buffer,4,1));
				if ($this->serverId() == 0) {
					// Client Node, set the server id to that of the answering server
					app_log("Reponse from Server: $serverIdIn",'trace');
					$this->serverId($serverIdIn);
				}
				elseif ($serverIdIn == 0 || $serverIdIn == 255) {
					// Server Node, we'll send the client our id
					// Do Nothing Here
				}
				elseif ($serverIdIn != $this->_serverId) {
					// This is for another server
					app_log("Server ID Mismatch: $serverIdIn != ".$this->_serverId,'error');
					//$this->error("Server ID Mismatch: $serverIdIn != ".$this->_serverId);
					return false;
				}

				// Check for Client ID Mismatch
				if ($this->session()->client()->id() != 0 && $this->session()->client()->id() != $this->_clientId) {
					app_log("Client ID Mismatch: ".$this->session()->client()->id()." != ".$this->_clientId,'error');
					$this->error("Client ID Mismatch: ".$this->session()->client()->id()." != ".$this->_clientId);
					return false;
				}
				elseif ($this->session()->client()->id() == 0) {
					// Set the Client ID
					app_log("Received new session information for client ".$this->_clientId,'debug');
					$this->session()->client()->id($this->_clientId);
					$this->session()->startTime(time());
					$this->session()->endTime(time()+86400);
					$this->session()->codeArray($this->_sessionCode);
				}

				app_log("Incoming: Client ID: ".$this->_clientId." Server ID: ".$serverIdIn." Length: ".$contentLength." Type: ".$this->_typeId." Session: ".$this->session()->codeDebug(),"debug");

				// Check for Terminators at end of data
				app_log("End of Header: ".ord(substr($buffer,$headerLength,1)),'trace');
				app_log("End of Content: ".ord(substr($buffer,$contentLength + $headerLength + 1,1)),'trace');

				$data = [];			// Array to hold incoming bytes
				app_log("Is request complete?",'trace');
				if (strlen($buffer) >= $contentLength + $this->_meta_chars && ord(substr($buffer,$headerLength,1)) == 2 && ord(substr($buffer,$contentLength + $headerLength + 1,1)) == 3) {
					$in = "";		// Incoming chars for debug output
					for ($i = 0; $i < $contentLength; $i++) {
						$in .= $i."[".ord(substr($buffer,$i+$headerLength + 1,1))."]";					
						$data[$i] = substr($buffer,$i+$headerLength + 1,1);
					}
					app_log("Bytes: ".$in);
					//$this->checksum(ord(substr($buffer,$length + 27,1)) * 256 + ord(substr($buffer,$length + 28,1)));

					// Remove Request from the Buffer
					app_log("Take request from buffer");
					$buffer = substr($buffer,$contentLength+$headerLength + 5);

					// Create the Message Instance and Parse the Data
					app_log("Parse message type $this->_typeId");

					$factory = new \Document\S4Factory();
					$this->_message = $factory->get($this->_typeId);
					//$this->_message->clientId($clientId);
					//$this->_message->serverId($serverId);
					//$message->sessionCode($sessionCode);
					if (empty($this->_message)) {
						$this->error("Failed to create message object: ".$factory->error());
						return false;
					}
					//app_log("Parsing contents of ".$this->_message->typeName());
					if ($this->_message->parse($data,$contentLength)) {
						// Return the Message
						app_log("Got me a message!");
						return true;
					}
					else {
						$this->error("Failed to Parse Message: ".$this->_message->error());
						return false;
					}
				}
				else if (strlen($buffer) >= $contentLength + 30) {
					// Full length but terminators are not in the right place
					// Drop 1st character and try again next loop
					app_log("End of Packet Terminators not found",'debug');
					$buffer = substr($buffer,1);
					return false;
				}
				else {
					// Not enough data yet
					return false;
				}
			}
			elseif (preg_match('/^\x{01}(..)(..)(..)(..)(.{'.$this->_sessionCodeLen.'})/',$buffer)) {
				print "Header not complete\n";
				print "Client ID: ".ord(substr($buffer,1,1)) * 256 + ord(substr($buffer,2,1))."\n";
				print "Server ID: ".ord(substr($buffer,3,1)) * 256 + ord(substr($buffer,4,1))."\n";
				print "Length: ".ord(substr($buffer,5,1)) * 256 + ord(substr($buffer,6,1))."\n";
				print "Type: ".ord(substr($buffer,7,1)) * 256 + ord(substr($buffer,8,1))."\n";
				print "Session: ";
				for ($i = 0; $i < $this->_sessionCodeLen; $i++) {
					print substr($buffer,9 + $i,1);
				}
				print "\n";
				print "Terminator: ".ord(substr($buffer,$headerLength+1,1))."\n";
				return false;
			}
			else {
				print "Terminators not found\n";
				for ($i = 0; $i < strlen($buffer); $i++) {
					print "[".ord(substr($buffer,$i,1))."]";
				}
				print "\n";
				$this->error("Terminators not found");
				$buffer = substr($buffer,1);
				return false;
			}
		}

		public function printChars($string): string {
			if (empty($string)) {
				return "Empty String";
			}
			$chars = "S4Engine::Engine::printChars():\n";
			for ($i = 0 ; $i < strlen($string) ; $i++) {
				$chars .= "[".ord(substr($string,$i,1))."]";
			}
			return $chars;
		}

		/**
		 * Set the message to be included in an envlop
		 * @param \Document\S4\Message
		*/
		public function setMessage(\Document\S4\Message $message) {
			$this->_message = $message;
		}

		/**
		 * Return the last set or extracted message
		 * @return \Document\S4\Message if available
		*/
		public function getMessage(): ?\Document\S4\Message {
			if (!empty($this->_message)) {
				if (is_object($this->_message)) return $this->_message;
				else {
					app_log("Not a \Document\S4\Message object",'error');
					return null;
				}
			}
			app_log("Message is empty",'error');
			return null;
		}

		/**
		 * Get the Client ID
		 * @param int Client ID
		 * @return int Client ID
		 */
		public function clientId(): int {
			return $this->_clientId;
		}

		/**
		 * Get the Client Object
		 * @return \S4Engine\Client
		 */
		public function client(): ?\S4Engine\Client {
			return $this->session()->client();
		}

		/**
		 * Get/Set the Server ID
		 * @param int Server ID
		 * @return int Server ID
		 */
		public function serverId(int $serverId = null): int {
			if (!is_null($serverId)) {
				app_log("Setting server id to $serverId",'info');
				$this->_serverId = $serverId;
			}
			return $this->_serverId;
		}

		/**
		 * Package message in a binary envelope
		 * @param string Output variable containing message content
		 * @return int Number of chars in message
		*/
		public function serialize(string &$string): int {
			if(empty($this->_message)) {
				$this->error("Message not set: use setMessage()");
				return -1;
			}
			if (!is_object($this->_message)) {
				$this->error("Message is not a \Document\s4\Message");
				return -1;
			}

			// Make sure we have a session assigned
			if (is_null($this->session())) {
				$this->error("Session not set");
				return -1;
			}

			$content = [];
			$contentLength = $this->_message->build($content);

			$chars = "Envelope Body: ";
			for ($i = 0; $i < $contentLength; $i++) {
				$chars .= "[".ord($content[$i])."]";
			}
			app_log($chars,'info');
			// Generate the Header for the envelope
			app_log("Outgoing: Client Id: ".$this->session()->client()->id()." Server Id: ".$this->serverId()." Length: $contentLength Type: ".$this->_message->typeName()." Session: ".$this->session()->codeDebug(),'debug');
			//app_log("CLIENTID: ".,'debug');
			//app_log("CLIENTNUM: ".$this->session()->client()->number(),'debug');
			//app_log("SESSIONID: ".$this->sessionCodeString(),'debug');
			$sessionCode = $this->session()->codeArray();
			$header = pack("C",1);										// 1 Byte Start of Text
			$header .= pack("n",$this->session()->client()->number());	// 2 Byte Client ID
			$header .= pack("n",$this->serverId());						// 2 Byte Server ID
			$header .= pack("n",$contentLength);						// 2 Byte Content Length
			$header .= pack("n",$this->_message->typeId());				// 2 Byte Content Type ID
			$header .= pack("C4", ...$sessionCode);						// 4 Byte Session Code
			$header .= pack("C",2);										// 1 Byte Start of Text

			// Combine the Header and Content
			$string = $header . implode("",$content);

			// Generate the Footer for the envelope
			$footer = pack("C",3) . pack("n",$this->_genChecksum($string)) . pack("C",4);
			//$footer = sprintf("%b%02b%b",3, $this->_genChecksum($string),4);
			$string .= $footer;

			$chars = "Entire Content: ";
			for ($i = 0; $i < strlen($string); $i++) {
				$chars .= "[".ord(substr($string,$i,1))."]";
			}
			app_log($chars,'info');

			return strlen($string);
		}

		/**
		 * Get the Session Code as an array
		 * @return array
		 */
		public function sessionCodeArray() {
			return $this->_sessionCode;
		}

		/**
		 * Get received session code
		 * @return string
		 */
		public function sessionCodeString(): string {
			return implode("",$this->_sessionCode);
		}

		/**
		 * Return session code as readable string
		 * @return string
		 */
		public function sessionCodeDebug(): string {
			$code = "";
			for ($i = 0; $i < $this->_sessionCodeLen; $i++) {
				$code .= "[".ord($this->_sessionCode[$i])."]";
			}
			return $code;
		}

		/**
		 * Get the full session key
		 * @return string
		 */
		public function sessionKey(): string {
			print "this->_clientId: ".$this->_clientId."\n";
			$sessionNum = $this->_sessionCode[0] * 256 * 256 * 256 + $this->_sessionCode[1] * 256 * 256 + $this->_sessionCode[2] * 256 + $this->_sessionCode[3];
			$code = sprintf("%04x%08x",$this->_clientId,$sessionNum);
			return $code;
		}

		/**
		 * Set the Session
		 * @param string 
		 */
		public function session(\S4Engine\Session $session = null): ?\S4Engine\Session {
			if (!is_null($session)) $this->_session = $session;
			return $this->_session;
		}

		/**
		 * Calculate Checksum
		 * @param string Content up to footer
		 * @return 2 byte checksum
		*/
		protected function _genChecksum($data): string {
			$checksum = 0;
			for ($i = 0; $i < strlen($data); $i++) {
				$checksum += ord(substr($data,$i,1));
			}
			$checksum = $checksum % 65536;
			return $checksum;
		}

		/**
		 * Return an Engine Status Summary
		 * @return string
		 */
		public function summary(): string {
			$summary = "Engine Summary\n";
			$summary .= "Client ID: ".$this->_clientId."\n";
			$summary .= "Server ID: ".$this->_serverId."\n";
			$summary .= "Type ID: ".$this->_typeId."\n";
			$summary .= "Session Code: ".$this->sessionCodeDebug()."\n";
			return $summary;
		}
	}
