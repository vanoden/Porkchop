<?php
	namespace Site\Logger;

	class Syslog Extends Log {
		private $host;

		public function __construct($parameters) {
			$this->syslog = true;
			$this->type = 'Syslog';
			parent::__construct($parameters);
		}

		public function connect(): true {
			if ($this->connected) return true;
			if (! defined('APPLICATION_LOG_NAME')) define('APPLICATION_LOG_NAME', 'Porkchop');
			openlog(APPLICATION_LOG_NAME, LOG_PID | LOG_PERROR, LOG_LOCAL1);
			$this->connected = true;
			return true;
		}

		public function write($message,$level = 'debug',$file = null,$line = null, $module = null, $view = null): bool {
			if (! $this->compares($level)) return 1;

			$message = preg_replace('/\t/',' ',$message);

			list($file,$line) = $this->caller($file,$line);

			syslog($this->posix_level($level), $this->formatted($message,$level,$file,$line));
			return true;
		}

		public function writeln($message,$level = 'debug',$file = null,$line = null, $module = null, $view = null): void {
			list($file,$line) = $this->caller($file,$line);

			$this->write($message,$level,$file,$line);
		}

		private function posix_level($level) {
			# PHP Syslog Levels (also for level validation)
			$syslog_xref = array(
				"emergency" => LOG_EMERG,
				"alert"		=> LOG_ALERT,
				"critical"	=> LOG_CRIT,
				"error"		=> LOG_ERR,
				"warning"	=> LOG_WARNING,
				"notice"	=> LOG_NOTICE,
				"info"		=> LOG_INFO,
				"debug"		=> LOG_DEBUG,
				"trace"		=> LOG_DEBUG
			);
			return $syslog_xref[$level];
		}
	}
