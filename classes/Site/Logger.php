<?php
	namespace Site;

	class Logger Extends \BaseClass {
		public $host = '';
		public $type = 'File';
		public $target = '';
		public $level = 'debug';

		public static function get_instance($parameters = array()) {
			if (! isset($parameters['level'])) $parameters['level'] = APPLICATION_LOG_LEVEL;

			if (strtolower($parameters['type']) == "syslog") {
				if (! isset($parameters['host'])) $parameters['host'] = '127.0.0.1';
				if (! isset($parameters['port'])) $parameters['port'] = 514;
				return new \Site\Logger\Syslog($parameters);
			} elseif (strtolower($parameters['type']) == "screen") {
				return new \Site\Logger\Screen($parameters);
			} elseif (strtolower($parameters['type']) == "file") {
				if (! isset($parameters['path'])) $parameters['path'] = APPLICATION_LOG;
				return new \Site\Logger\File($parameters);
			} elseif (strtolower($parameters['type']) == "errorlog") {
				return new \Site\Logger\Errorlog($parameters);
			} elseif (isset($parameters['path']) && ! empty($parameters['path'])) {
				return new \Site\Logger\File($parameters);
			} elseif (defined(APPLICATION_LOG)) {
				return new \Site\Logger\File($parameters);
			} else {
				return new \Site\Logger\Errorlog($parameters);
			}
		}
	}
