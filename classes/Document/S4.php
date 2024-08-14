<?php
	namespace Document;

	/**
	 * This class represents the Envelope Containing an S4 Request/Response
	 * S4 Packet Format
	 * 1 byte - Start Terminator
	 * 2 bytes - Client ID
	 * 2 bytes - Server ID
	 * 2 bytes - Length
	 * 2 bytes - Type
	 * 16 bytes - Session Code
	 * 1 byte - Start of Text
	 * n bytes - Data
	 * 1 byte - End of Text
	 * 2 byte - checksum
	 * 1 byte - End Terminator
	 * 
	 * [1][0][1][9][9][0][5][0][1][0][1][2][3][4][5][6][7][8][9][10][11][12][13][14][15][2][5][5][5][5][5][3][1][2][4]
	 */
	class S4 Extends \BaseClass {
		protected $_clientId = 0;
		protected $_serverId = 0;
		protected $_sessionCode;		// 16 Byte Session Code
		protected $_message;			// Message contained in envelope
		protected $_checksum;			// 2 Byte Checksum
		protected $_meta_chars = 30;	// Number of header, footer and delimiter chars for completion checking

		/**
		 * Constructor
		 */
		public function __construct() {
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
			for ($i = 0; $i < strlen($buffer); $i++) {
				$byteString .= "[".ord(substr($buffer,$i,1))."]";
			}
			app_log("Parsing: $byteString","trace");
	
			// Check for starting terminator
			while (strlen($buffer) > 0 && ord(substr($buffer,0,1)) != 1) {
				app_log("Dropping 1st character","debug");
				$buffer = substr($buffer,1);
			}
			if (strlen($buffer) == 0) {
				$this->error("No data to parse");
				return false;
			}

			app_log("Got Start Terminator",'trace');
	
			// Check for a minimal complete packet length, including header and footer
			// Minimum packet length is 30 bytes
			if (strlen($buffer) < 26) {
				$byteString = "";
				$this->error("Not enough data yet for header, only ".strlen($buffer)." chars");
				for ($i = 0; $i < strlen($buffer); $i++) {
					$byteString .= "[".ord(substr($buffer,$i,1))."]";
				}
				app_log("Buffer: $byteString","trace");
				return false;
			}
			app_log("Start of text? ".ord(substr($buffer,25,1)),'trace');
			if (ord(substr($buffer,25,1)) != 2) {
				$this->error("Missing Start of Text. Dropping 1st character");
				return false;
			}

			$contentLength = ord(substr($buffer,5,1)) * 256 + ord(substr($buffer,6,1));
			app_log("Expecting ".$contentLength." chars of data");

			app_log("Got " . strlen($buffer) . " of " . ($contentLength + $this->_meta_chars) . " bytes");
	
			// Check for a minimal complete header using terminators
			if (ord(substr($buffer,0,1)) == 1 && ord(substr($buffer,25,1)) == 2) {
				print "Parsing Header\n";
				if (strlen($buffer) < $contentLength + $this->_meta_chars) {
					print "Not enough data yet for body, only ".strlen($buffer)." chars\n";
					return false;
				}
	
				app_log("SOH: ".ord(substr($buffer,0,1)),'trace');
				app_log("SOT: ".ord(substr($buffer,25,1)),'trace');
				app_log("CID: [" . ord(substr($buffer,1,1)) . "][" . ord(substr($buffer,2,1)) . "]",'trace');
				app_log("SID: [" . ord(substr($buffer,3,1)) . "][" . ord(substr($buffer,4,1)),'trace');
				$clientId = ord(substr($buffer,1,1)) * 256 + ord(substr($buffer,2,1));
				$serverId = ord(substr($buffer,3,1)) * 256 + ord(substr($buffer,4,1));
				$typeId = ord(substr($buffer,7,1)) * 256 + ord(substr($buffer,8,1));
				$sessionCode = array();
				for ($i = 0; $i < 16; $i++) {
					$int = ord(substr($buffer,$i + 9,1));
					$int0 = number_format(($int / 16),0);
					$int1 =  $int % 16;
					print "[$int] $int0 $int1\n";
					$sessionCode[$i * 2] = sprintf("%0X",$int0);
					$sessionCode[$i * 2 + 1] = sprintf("%0X",$int1);
				}
				$session_code = implode('',$sessionCode);

				app_log("Client ID: ".$clientId,"debug");
				app_log("Server ID: ".$serverId,"debug");
				app_log("Length: ".$contentLength,"debug");
				app_log("Type: ".$typeId,"debug");
				//app_log("Session Code: ".$sessionCode,"debug");

				// Check for Terminators at end of data
				print "End of Header: ".ord(substr($buffer,25,1))."\n";
				print "End of Content: ".ord(substr($buffer,$contentLength + 26,1))."\n";
				$data = [];
				if (strlen($buffer) >= $contentLength + $this->_meta_chars && ord(substr($buffer,25,1)) == 2 && ord(substr($buffer,$contentLength + 26,1)) == 3) {
					for ($i = 0; $i < $contentLength; $i++) {
						print $i."[".ord(substr($buffer,$i+26,1))."]";					
						$data[$i] = substr($buffer,$i+26,1);
					}
					print "\n";
					//$this->checksum(ord(substr($buffer,$length + 27,1)) * 256 + ord(substr($buffer,$length + 28,1)));

					// Remove Request from the Buffer
					$buffer = substr($buffer,$contentLength+30);

					// Create the Message Instance and Parse the Data
					$factory = new \Document\S4Factory();
					$this->_message = $factory->get($typeId);
					//$this->_message->clientId($clientId);
					//$this->_message->serverId($serverId);
					//$message->sessionCode($sessionCode);
					if ($this->_message->parse($data,$contentLength)) {
						// Return the Message
						return true;
					}
					else {
						$this->error("Failed to Parse Message");
						return false;
					}
				}
				else if (strlen($buffer) >= $contentLength + 30) {
					// Full length but terminators are not in the right place
					// Drop 1st character and try again next loop
					print "End of Packet Terminators not found\n";
					$buffer = substr($buffer,1);
					return false;
				}
				else {
					// Not enough data yet
					return false;
				}
			}
			elseif (preg_match('/^\x{01}(..)(..)(..)(..)(.{16})/',$buffer)) {
				print "Header not complete\n";
				print "Client ID: ".ord(substr($buffer,1,1)) * 256 + ord(substr($buffer,2,1))."\n";
				print "Server ID: ".ord(substr($buffer,3,1)) * 256 + ord(substr($buffer,4,1))."\n";
				print "Length: ".ord(substr($buffer,5,1)) * 256 + ord(substr($buffer,6,1))."\n";
				print "Type: ".ord(substr($buffer,7,1)) * 256 + ord(substr($buffer,8,1))."\n";
				print "Session: ";
				for ($i = 0; $i < 16; $i++) {
					print substr($buffer,9 + $i,1);
				}
				print "\n";
				print "Terminator: ".ord(substr($buffer,25,1))."\n";
				return false;
			}
			else {
				print "Terminators not found\n";
				for ($i = 0; $i < strlen($buffer); $i++) {
					print "[".ord(substr($buffer,$i,1))."]";
				}
				print "\n";
				$buffer = substr($buffer,1);
				return false;
			}
		}

		public function arrayPrint($string) {
			for ($i = 0 ; $i < strlen($string) ; $i++) {
				print "[".ord(substr($string,$i,1))."]";
			}
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
			if (!empty($message) && is_object($message)) return $message;
			return null;
		}

		/**
		 * Get/Set the Client ID
		 * @param int Client ID
		 * @return int Client ID
		 */
		public function clientId($clientId = null): int {
			if (!is_null($clientId)) $this->_clientId = $clientId;
			return $this->_clientId;
		}

		/**
		 * Get/Set the Server ID
		 * @param int Server ID
		 * @return int Server ID
		 */
		public function serverId($serverId = null): int {
			if (!is_null($serverId)) $this->_serverId = $serverId;
			return $this->_serverId;
		}

		/**
		 * Package message in a binary envelope
		 * @param string Output variable containing message content
		 * @return int Number of chars in message
		*/
		public function serialize(&$string): int {
			if(empty($this->_message)) {
				$this->error("Message not set: use setMessage()");
				return -1;
			}
			if (!is_object($this->_message)) {
				$this->error("Message is not a \Document\s4\Message");
				return -1;
			}
			$content = "";
			$contentLength = $this->_message->build($content);
			print("Content[".$contentLength."]: ".$content."\n");

			// Generate the Header for the envelope
			$header = pack("C",1) . pack("n",$this->clientId()) . pack("n",$this->serverId()) . pack("n",$contentLength) . pack("n",$this->_message->typeId()) . $this->sessionCode() . pack("C",2);
			$string = $header . $content;

			// Generate the Footer for the envelope
			$footer = pack("C",3) . pack("n",$this->_genChecksum($string)) . pack("C",4);
			//$footer = sprintf("%b%02b%b",3, $this->_genChecksum($string),4);
			$string .= $footer;

			return strlen($string);
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
		 * Write the Session Code
		 */
		protected function sessionCode() {
			return pack("CCCCCCCCCCCCCCCC",0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15);
			//return sprintf("%016b",123454321);
		}
	}
