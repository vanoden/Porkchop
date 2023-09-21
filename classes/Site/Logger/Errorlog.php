<?php
	namespace Site\Logger;

	class Errorlog Extends Log {
		private $host;

		public function __construct($parameters) {
			parent::__construct($parameters);
		}

		public function connect() {
			return 1;
		}

		public function write($message,$level = 'debug',$file = null,$line = null) {
			if (! $this->compares($level)) return 1;

			list($file,$line) = $this->caller($file,$line);

			error_log($this->formatted($message,$level,$file,$line));
			return 1;
		}

		public function writeln($message,$level = 'debug',$file = null,$line = null) {
			list($file,$line) = $this->caller($file,$line);

			$this->write($message,$level,$file,$line);
		}
	}
