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

		public function connect(): bool {
			return true;
		}

		public function write($message,$level = 'debug',$file = null,$line = null, $module = null, $view = null): bool {
			if (! $this->compares($level)) return true;
			list($file,$line) = $this->caller($file,$line);

			print_r($this->formatted($message,$level,$file,$line,$module,$view));
			return true;
		}

		public function writeln($message,$level = 'debug',$file = null,$line = null, $module = null, $view = null): void {
			list($file,$line) = $this->caller($file,$line);
			if ($this->linefeed) {
				$message .= "\n";
			}
			$this->write($message,$level,$file,$line,$module,$view);
		}
	}
