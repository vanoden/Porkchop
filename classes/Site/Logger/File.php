<?php
	namespace Site\Logger;

	class File Extends Log {
		private $path;
		private $fh;

		public function __construct($parameters) {
			if (isset($parameters['path'])) {
				$this->path = $parameters['path'];
			}
			else {
				$this->error = "Path required";
			}

			if (isset($parameters['level'])) {
				$this->level = $parameters['level'];
			}
		}

		public function connect() {
			if ($this->connected) return true;
			try {
				$this->fh = fopen($this->path,'a');
				$this->connected = true;
				return 1;
			}
			catch (Exception $e) {
				$this->connected = false;
				$this->error = $e->getMessage();
				return 0;
			}
		}

		public function write($message,$level = 'debug',$file = null,$line = null) {
			if (! $this->compares($level)) return 1;
			list($file,$line) = $this->caller($file,$line);

			fwrite($this->fh,$this->formatted($message,$level,$file,$line));
			return 1;
		}

		public function writeln($message,$level = 'debug',$file = null,$line = null) {
			list($file,$line) = $this->caller($file,$line);

			$this->write($message."\n",$level,$file,$line);
		}
	}
?>
