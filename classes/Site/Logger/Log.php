<?php
	namespace Site\Logger;

	class Log {
		public $error = null;
		public $level = 'debug';
		public $connected = false;

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

		public function formatted($message,$level = 'debug',$file,$line) {
			# Replace Carriage Returns
			$message = preg_replace('/\r*\n/',"\n",$message);
	
			# Prepare Values for String
			$date = $this->datetime();
			$file = preg_replace('#^'.BASE.'/#','',$file);
			$pid = getMyPid();

			if ((array_key_exists('_page',$GLOBALS)) and (property_exists($GLOBALS['_page'],'module'))) $module = $GLOBALS['_page']->module;
			else $module = '-';
			if ((array_key_exists('_page',$GLOBALS)) and (property_exists($GLOBALS['_page'],'view'))) $view = $GLOBALS['_page']->view;
			else $view = '-';
			if (array_key_exists('_SESSION_',$GLOBALS)) {
				if (property_exists($GLOBALS['_SESSION_'],'id')) $session_id = $GLOBALS['_SESSION_']->id;
				else $session_id = '-';
				if (isset($GLOBALS['_SESSION_']->customer) && is_numeric($GLOBALS['_SESSION_']->customer->id)) $customer_id = $GLOBALS['_SESSION_']->customer->id;
				else $customer_id = '-';
			}
			else {
				$session_id = '-';
				$customer_id = '-';
			}

			# Build String
			return "$date [$pid] [$level] $module::$view $file:$line $session_id $customer_id $message\n";
		}

		public function compares ($level = "debug") {
			# Filter on log level
			if ($this->level == "trace") return true;
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

		public function error() {
			return $this->error;
		}
	}
?>
