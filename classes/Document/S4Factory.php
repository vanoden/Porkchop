<?php
	namespace Document;

	// S4 Packet Format
	// 1 byte - Start Terminator
	// 2 bytes - Client ID
	// 2 bytes - Server ID
	// 2 bytes - Length
	// 2 bytes - Type
	// 16 bytes - Session Code
	// 1 byte - Start of Text
	// n bytes - Data
	// 1 byte - End of Text
	// 2 byte - checksum
	// 1 byte - End Terminator

	class S4Factory Extends \BaseClass {
		protected $_format = 's4';
		protected $_clientId;
		protected $_serverId;
		protected $_typeId;
		protected $_sequenceId;
		protected $_length;
		protected $_sessionId;
		protected $_request;
		protected $_data = [];
		protected $_meta_chars = 30;

		public function __construct($format = 's4') {
			$this->_format = $format;
		}

		public function parseEnvelope(&$buffer): bool {
			if (strlen($buffer) == 0) {
				return false;
			}

			for ($i = 0; $i < strlen($buffer); $i++) {
				print "[".ord(substr($buffer,$i,1))."]";
			}
			print "\n";

			// Packet Format
			// 1 byte - Start Terminator
			// 2 bytes - Client ID
			// 2 bytes - Server ID
			// 2 bytes - Length
			// 2 bytes - Type
			// 16 bytes - Session Code
			// 1 byte - Start of Text
			// n bytes - Data
			// 1 byte - End of Text
			// 2 byte - checksum
			// 1 byte - End Terminator
	
			// Check for starting terminator
			if (strlen($buffer) > 0 && ord(substr($buffer,0,1)) != 1) {
				print "Dropping 1st character\n";
				$buffer = substr($buffer,1);
				return false;
			}
			print "Got Start Terminator\n";
	
			// Check for a minimal complete packet length, including header and footer
			// Minimum packet length is 30 bytes
			if (strlen($buffer) < 26) {
				print "Not enough data yet for header, only ".strlen($buffer)." chars\n";
				for ($i = 0; $i < strlen($buffer); $i++) {
					print "[".ord(substr($buffer,$i,1))."]";
				}
				print "\n";
				return false;
			}
			if (ord(substr($buffer,25,1)) != 2) {
				print "Missing Start of Text. Dropping 1st character\n";
				return false;
			}
			$this->_length = ord(substr($buffer,7,1)) * 256 + ord(substr($buffer,8,1));
		
			print "Got " . strlen($buffer) . " of " . ($this->_length + $this->_meta_chars) . " bytes\n";
	
			// Check for a minimal complete header using terminators
			if (preg_match('/^\x{01}.{24}\x{02}/',$buffer)) {
				print "Parsing Header\n";
				if (strlen($buffer) < $this->_length + $this->_meta_chars) {
					print "Not enough data yet for body, only ".strlen($buffer)." chars\n";
					return false;
				}
	
				print "SOH: ".ord(substr($buffer,0,1))."\n";
				print "SOT: ".ord(substr($buffer,25,1))."\n";
				print "CID: [" . ord(substr($buffer,1,1)) . "][" . ord(substr($buffer,2,1)) . "]\n";
				print "SID: [" . ord(substr($buffer,3,1)) . "][" . ord(substr($buffer,4,1)) . "]\n";
				$this->_clientId = ord(substr($buffer,1,1)) * 256 + ord(substr($buffer,2,1));
				$this->_serverId = ord(substr($buffer,3,1)) * 256 + ord(substr($buffer,4,1));
				$this->_typeId = ord(substr($buffer,5,1)) * 256 + ord(substr($buffer,6,1));
				$session_code = array();
				for ($i = 0; $i < 16; $i++) {
					$int = ord(substr($buffer,$i + 9,1));
					$int0 = number_format(($int / 16),0);
					$int1 =  $int % 16;
					print "[$int] $int0 $int1\n";
					$session_code[$i * 2] = sprintf("%0X",$int0);
					$session_code[$i * 2 + 1] = sprintf("%0X",$int1);
				}
				$session_code = implode('',$session_code);

				print "Client ID: ".$this->_clientId."\n";
				print "Server ID: ".$this->_serverId."\n";
				print "Length: ".$this->_length."\n";
				print "Type: ".$this->_typeId."\n";
				print "Session Code: $session_code\n";

				// Check for Terminators at end of data
				print "End of Header: ".ord(substr($buffer,25,1))."\n";
				print "End of Content: ".ord(substr($buffer,$this->_length + 26,1))."\n";
				if (strlen($buffer) >= $this->_length + $this->_meta_chars && ord(substr($buffer,25,1)) == 2 && ord(substr($buffer,$this->_length + 26,1)) == 3) {
					for ($i = 0; $i < $this->_length; $i++) {
						print $i."[".ord(substr($buffer,$i+26,1))."]";					
						$this->_data[$i] = substr($buffer,$i+26,1);
					}
					print "\n";
					//$this->checksum(ord(substr($buffer,$length + 27,1)) * 256 + ord(substr($buffer,$length + 28,1)));

					// Remove Request from the Buffer
					$buffer = substr($buffer,$this->_length+30);
					return true;
				}
				else if (strlen($buffer) >= $this->_length + 30) {
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

		public function typeName(): string {
			return "Unknown Request Type";
		}

		public function getRequest() {
			print "Type ID: ".$this->_typeId."\n";
			if ($this->_typeId == 1) {
				$request = new \Document\S4\PingRequest();
				if ($request->parse($this->_data)) {
					return $request;
				}
				else {
					$this->error("Cannot parse PingRequest: " . $request->error());
					return null;
				}
				return $request;
			}
			elseif ($this->_typeId == 5) {
				$request = new \Document\S4\ReadingRequest();
				if ($request->parse($this->_data)) {
					return $request;
				}
				else {
					$this->error("Cannot parse ReadingRequest: " . $request->error());
					return null;
				}
				return $request;
			}
		}

		public function uri($type_id) {
			switch ($type_id) {
				case 1:
					return "/api/register/ping";
				case 2:
					return "/api/register/authenticateSession";
				case 3:
					return "/api/register/me";
				case 4:
					return "/api/monitor/ping";
				case 5:
					return "/api/monitor/getAsset";
				case 6:
					return "/api/monitor/getSensor";
				case 7:
					return "/api/monitor/addReading";
				case 8:
					return "/api/monitor/addMessage";
				case 9:
					return "/api/monitor/addFault";
				case 10:
					return "/api/monitor/addEvent";
			}
		}
	}