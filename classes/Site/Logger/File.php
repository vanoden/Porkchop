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
			if (is_dir($this->path)) {
				if (! preg_match('/\/$/',$this->path)) $this->path .= "/";
				$this->path .= "application.log";
			}
		}

		public function connect() {
			if ($this->connected) return true;
			try {
				$this->fh = fopen($this->path,'a');
			}
			catch (Exception $e) {
				$this->connected = false;
				$this->error = $e->getMessage();
				return false;
			}

			if (!$this->fh) {
				$this->error = join("\n",error_get_last());
				return false;
			}
			$this->connected = true;
			return true;
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
