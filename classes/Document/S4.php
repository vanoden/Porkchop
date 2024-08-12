<?php
	namespace Document\S4;

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
		protected $_sessionCode;		// 16 Byte Session Code
		protected $_meta_chars = 30;	// Number of header, footer and delimiter chars for completion checking

		/**
		 * Constructor
		 */
		public function __construct() {
		}

		/**
		 * Parse the binary envelope
		 * @param reference to buffer
		 * @return \Document\S4\Message if parsed successfully, else null
		*/
		public function parse(&$buffer): \Document\S4\Message {
			if (strlen($buffer) == 0) {
				$this->error("No data to parse");
				return false;
			}

			for ($i = 0; $i < strlen($buffer); $i++) {
				print "[".ord(substr($buffer,$i,1))."]";
			}
			print "\n";
	
			// Check for starting terminator
			while (strlen($buffer) > 0 && ord(substr($buffer,0,1)) != 1) {
				app_log("Dropping 1st character","info");
				$buffer = substr($buffer,1);
			}
			if (strlen($buffer) == 0) {
				$this->error("No data to parse");
				return false;
			}

			print "Got Start Terminator\n";
	
			// Check for a minimal complete packet length, including header and footer
			// Minimum packet length is 30 bytes
			if (strlen($buffer) < 26) {
				$this->error("Not enough data yet for header, only ".strlen($buffer)." chars");
				for ($i = 0; $i < strlen($buffer); $i++) {
					print "[".ord(substr($buffer,$i,1))."]";
				}
				print "\n";
				return null;
			}
			print "Start of text? ".ord(substr($buffer,25,1))."\n";
			if (ord(substr($buffer,25,1)) != 2) {
				$this->error("Missing Start of Text. Dropping 1st character");
				return null;
			}

			$contentLength = ord(substr($buffer,5,1)) * 256 + ord(substr($buffer,6,1));
			app_log("Expecting ".$contentLength." chars of data");

			app_log("Got " . strlen($buffer) . " of " . ($contentLength + $this->_meta_chars) . " bytes");
	
			// Check for a minimal complete header using terminators
			if (ord(substr($buffer,0,1)) == 1 && ord(substr($buffer,25,1)) == 2) {
				print "Parsing Header\n";
				if (strlen($buffer) < $contentLength + $this->_meta_chars) {
					print "Not enough data yet for body, only ".strlen($buffer)." chars\n";
					return null;
				}
	
				print "SOH: ".ord(substr($buffer,0,1))."\n";
				print "SOT: ".ord(substr($buffer,25,1))."\n";
				print "CID: [" . ord(substr($buffer,1,1)) . "][" . ord(substr($buffer,2,1)) . "]\n";
				print "SID: [" . ord(substr($buffer,3,1)) . "][" . ord(substr($buffer,4,1)) . "]\n";
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
				app_log("Session Code: $sessionCode","debug");

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
					$message = $factory->get($typeId);
					$message->clientId($clientId);
					$message->serverId($serverId);
					//$message->sessionCode($sessionCode);
					if ($message->parse($data,$contentLength)) {
						// Return the Message
						return $message;
					}
					else {
						$this->error("Failed to Parse Message");
						return null;
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

		/**
		 * Package message in a binary envelope
		*/
		public function serialize($message): string {
			$data = $message->serialize();
			$header = sprintf("%0b%02b%02b%02b%016b%b",$message->clientId(),$message->serverId(),$message->typeId(),$message->sessionCode(),$message->length(),1);
			return $this->footer($header.$data);
			return $header . $data . $footer;
		}

		/**
		 * Append Footer with Calculated Checksum
		 * @param string $data
		 * @return string
		 */
		public function footer($data): string {
			$checksum = 0;
			for ($i = 0; $i < strlen($data); $i++) {
				$checksum += ord(substr($data,$i,1));
			}
			$checksum = $checksum % 65536;
			$footer = sprintf("%02b%02b%0b",$checksum / 256,$checksum % 256,4);
			return $data . $footer;
		}
	}