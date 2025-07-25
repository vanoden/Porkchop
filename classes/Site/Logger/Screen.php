<?php
	namespace Site\Logger;

	class Screen Extends Log {

		public function __construct($parameters) {
			if (isset($parameters['html']) && $parameters['html']) {
				$this->html = true;
			}
			if (isset($parameters['level'])) {
				$this->level = $parameters['level'];
			}
			if (isset($parameters['linefeed'])) {
				$this->linefeed = $parameters['linefeed'];
			}
			$this->type = 'Screen';
			parent::__construct($parameters);
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
			if ($this->linefeed) {
				$message .= "\n";
			}
			$this->write($message,$level,$file,$line);
		}
	}
