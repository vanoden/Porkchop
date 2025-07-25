<?php
	namespace Site\Logger;

	class Log Extends \BaseClass {
		public $level = 'debug';
		public $connected = false;
		public $html = false;
		public $syslog = false;
		public $type = 'File';
		public $linefeed = true;

		public function __construct($parameters = []) {
			if (isset($parameters['level'])) {
				$this->level = $parameters['level'];
			}
		}

		public function caller($file,$line) {
			if (isset($file)) return array($file,$line);

			$trace = debug_backtrace();
			$caller = $trace[1];
			$file = $caller['file'];
			$line = $caller['line'];
			return array($file,$line);
		}
		public function datetime($format = "Y/m/d H:i:s") {
			return date($format);
		}

		public function formatted($message,$level = 'debug',$file = null,$line = null) {
			# Replace Carriage Returns
			$message = preg_replace('/\r*\n$/',"",$message);
	
			# Prepare Values for String
			$date = $this->datetime();
			$file = preg_replace('#^'.BASE.'/#','',$file);
			$pid = getMyPid();

			if ((array_key_exists('_page',$GLOBALS)) and (property_exists($GLOBALS['_page'],'module'))) $module = $GLOBALS['_page']->module;
			else $module = 'core';
			if ((array_key_exists('_page',$GLOBALS)) and (property_exists($GLOBALS['_page'],'view'))) $view = $GLOBALS['_page']->view;
			else $view = 'index';
			if (array_key_exists('_SESSION_',$GLOBALS)) {
				if (!is_null($GLOBALS['_SESSION_']) && property_exists($GLOBALS['_SESSION_'],'id')) $session_id = $GLOBALS['_SESSION_']->id;
				else $session_id = '-';
				if (isset($GLOBALS['_SESSION_']->customer) && is_numeric($GLOBALS['_SESSION_']->customer->id)) $customer_id = $GLOBALS['_SESSION_']->customer->id;
				else $customer_id = '-';
			}
			else {
				$session_id = '-';
				$customer_id = '-';
			}

			# Build String
			if ($this->html) {
				return "<div class=\"logger_line\"><span class='logger date'>$date</span><span class='logger pid'>$pid</span> <span class='logger level'>$level</span> <span class='logger module_view'><span class='logger module'>$module</span>::<span class='logger view'>$view</span></span> <span class='logger file'>$file</span>:<span class='logger line'>$line</span> <span class='logger session'>$session_id</span> <span class='logger customer'>$customer_id</span> <span class='logger message'>$message</span></div>\n";
			}
			elseif ($this->syslog) {
				return "[$level] $module::$view $file:$line $session_id $customer_id $message";
			}
			else {
				return "$date [$pid] [$level] $module::$view $file:$line $session_id $customer_id $message\n";
			}
		}

		public function compares ($level = "debug") {
			# Filter on log level
			if ($this->level == "trace2") return true;
			elseif ($this->level == "trace"		&& in_array($level,array('trace','debug','info','notice','warning','error','critical','alert','emergency'))) return true;
			elseif ($this->level == "debug"		&& in_array($level,array('debug','info','notice','warning','error','critical','alert','emergency'))) return true;
			elseif ($this->level == "info"		&& in_array($level,array('info','notice','warning','error','critical','alert','emergency'))) return true;
			elseif ($this->level == "notice"	&& in_array($level,array('notice','warning','error','critical','alert','emergency'))) return true;
			elseif ($this->level == "warning"	&& in_array($level,array('warning','error','critical','alert','emergency'))) return true;
			elseif ($this->level == "error"		&& in_array($level,array('error','critical','alert','emergency'))) return true;
			elseif ($this->level == "critical"	&& in_array($level,array('critical','alert','emergency'))) return true;
			elseif ($this->level == "alert"		&& in_array($level,array('alert','emergency'))) return true;
			elseif ($this->level == "emergency"	&& in_array($level,array('emergency'))) return true;
			return false;
		}


		public function level($level = null): string {
			if (isset($level)) {
				if (! $this->validLevel($level)) {
					throw new \Exception("Invalid log level: ".$level);
				}
				$this->level = $level;
			}
			return $this->level;
		}

		public function validLevel($level): bool {
			if (is_string($level)) {
				$level = strtolower($level);
				if (in_array($level, array('debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'))) {
					return true;
				}
			}
			return false;
		}
	}
