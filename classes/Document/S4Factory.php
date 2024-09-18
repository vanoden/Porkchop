<?php
	namespace Document;

	class S4Factory Extends \BaseClass {
		protected $_format = 's4';
		protected $_clientId;			// Integer identifying client
		protected $_serverId;			// Integer identifying server
		protected $_typeId;				// Integer identifying message type
		protected $_sequenceId;			// Integer message sequence id
		protected $_length;				// Integer length of message content
		protected $_sessionId;			// Integer Session ID
		protected $_request;			// Request Object
		protected $_data = [];			// Array of message content bytes

		/**
		 * Constructor
		 * @params string Optional document format
		*/
		public function __construct($format = 's4') {
			$this->_format = $format;
		}

		/**
		 * Create a new message instance.
		 * @params string Class name
		 * @return Document::S4::Message
		*/
		public function create($type): ? \Document\S4\Message {
			if ($type == 'RegisterRequest') {
				return new \Document\S4\RegisterRequest();
			}
			elseif ($type == 'RegisterResponse') {
				return new \Document\S4\RegisterResponse();
			}
			elseif ($type == 'PingRequest') {
				return new \Document\S4\PingRequest();
			}
			elseif ($type == 'PingResponse') {
				return new \Document\S4\PingResponse();
			}
			elseif ($type == 'ReadingPost') {
				return new \Document\S4\ReadingPost();
			}
			elseif ($type == 'Acknowledgement') {
				return new \Document\S4\Acknowledgement();
			}
			elseif ($type == "AuthRequest") {
				return new \Document\S4\AuthRequest();
			}
			elseif ($type == "TimeResponse") {
				return new \Document\S4\TimeResponse();
			}
			else {
				$this->error("Invalid message type");
				return null;
			}
		}

		/**
		 * Return the request document associated with the type id
		 * used to parse the incoming request.
		 * @return Document::S4::Request
		*/
		public function get(int $typeId) {
			if ($typeId == 1) {
				return new \Document\S4\RegisterRequest();
			}
			elseif ($typeId == 2) {
				return new \Document\S4\RegisterResponse();
			}
			elseif ($typeId == 3) {
				return new \Document\S4\PingRequest();
			}
			elseif ($typeId == 4) {
				return new \Document\S4\PingResponse();
			}
			elseif ($typeId == 5) {
				return new \Document\S4\ReadingPost();
			}
			elseif ($typeId == 7) {
				return new \Document\S4\Acknowledgement();
			}
			else if ($typeId == 10) {
				return new \Document\S4\BadRequestResponse();
			}
			elseif ($typeId == 12) {
				return new \Document\S4\TimeResponse();
			}
			elseif ($typeId == 13) {
				return new \Document\S4\AuthRequest();
			}
			elseif ($typeId == 14) {
				return new \Document\S4\AuthResponse();
			}
			elseif ($typeId == 19) {
				return new \Document\S4\Unauthorized();
			}
			else {
				$this->error("Invalid message type");
				return null;
			}
		}

		/**
		 * Get the URI associated with a given request type id
		 * This is used to identify the API endpoint for the given request
		 * @param int type_id Unique integer representing a type of call
		 * @return string representing URI to parse for module/view
		*/	
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
