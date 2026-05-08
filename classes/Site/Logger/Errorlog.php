<?php
	namespace Site\Logger;

	class Errorlog Extends Log {
		private $host;

		public function __construct($parameters) {
			$this->type = 'Errorlog';
			parent::__construct($parameters);
		}

		public function connect(): bool {
			return true;
		}

		public function write($message,$level = 'debug',$file = null,$line = null, $module = null, $view = null): bool {
			if (! $this->compares($level)) return true;

			list($file,$line) = $this->caller($file,$line);

			error_log($this->formatted($message,$level,$file,$line,$module,$view));
			return true;
		}

		public function writeln($message,$level = 'debug',$file = null,$line = null, $module = null, $view = null): void {
			list($file,$line) = $this->caller($file,$line);

			$this->write($message,$level,$file,$line,$module,$view);
		}
	}
