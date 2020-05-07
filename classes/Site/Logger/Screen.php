<?php
	namespace Site\Logger;

	class Screen Extends Log {

		public function __construct($parameters) {
			if (isset($parameters['level'])) {
				$this->level = $parameters['level'];
			}
		}

		public function connect() {
			return true;
		}

		public function write($message,$level = 'debug',$file = null,$line = null) {
			if (! $this->compares($level)) return 1;
			list($file,$line) = $this->caller($file,$line);

			print_r($this->formatted($message,$level,$file,$line));
			return 1;
		}

		public function writeln($message,$level = 'debug',$file = null,$line = null) {
			list($file,$line) = $this->caller($file,$line);

			$this->write($message."\n",$level,$file,$line);
		}

		public function error() {
			return $this->error;
		}
	}
