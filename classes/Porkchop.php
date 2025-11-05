<?php
	class Porkchop {
		# Application Logging (leave error_log for system errors)
		public function app_log($message, $level = 'debug', $path = null, $line = null) {
			if (! isset($path)) {
				$trace = debug_backtrace();
				$caller = $trace[0];
				$path = $caller['file'];
				$line = $caller['line'];
			}
			$GLOBALS['logger']->writeln($message,$level,$path,$line);
		}

		public function datetime($date = null,$range=0) {
			if (empty($date)) {
				$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1];
				$ident = $caller['class']."::".$caller['function']." (".$caller['file'].":".$caller['line'].")";
				app_log("getMySQLDate() received empty date from $ident",'info');
				return null;
			}

			# Handle Some Keywords
			if (preg_match("/today/i",$date)) return date("Y-m-d");
			if (preg_match("/now/i",$date)) return date("Y-m-d h:i:s");
			if (preg_match("/tomorrow/i",$date)) return date("Y-m-d",time() + 86400);
			if (preg_match('/^(19|20|21|22)\d{2}$/',$date)) return $date."-01-01 00:00:00";

			# Handle OffSets
			if (preg_match('/(\+|\-)\s*(\d+)\s*(hour|day|week)s?/i',$date,$matches)) {
				$offset = $matches[1];
				$unit = $matches[2];
				error_log("Offset: $offset Unit: $unit");
				if (strtolower($unit) == 'hour')
					$adjust = 3600 * $offset;
				elseif (strtolower($unit) == 'day')
					$adjust = 86400 * $offset;
				elseif (strtolower($unit) == 'week')
					$adjust = 604800 * $offset;
				$newdate = date("Y-m-d h:i:s",time() + $adjust);
				app_log("get_mysql_date received $date, returns $newdate",'debug');
				return $newdate;
			}

			# T Dates
			if (preg_match('/^\d\d\d\d\-\d\d\-\d\dT/',$date)) {
				$date = preg_replace('/T/',' ',$date);
			}

			# Ignore Empty Dates
			if (! preg_match("/^[\d\-\/\:\s]+.$/",$date)) {
				app_log("get_mysql_date found invalid date '$date', returns 0",'notice');
				return null;
			}
			if (preg_match("/^0+\/0+\/0+\/$/",$date)) return "0000-00-00";

			# Already SQL Formatted
			if (preg_match('/^\d\d\d\d\-\d\d\-\d\d/',$date)) {
				app_log("get_mysql_date returns $date",'debug',__FILE__,__LINE__);
				return $date;
			}

			# Unix Timestamp
			if (preg_match('/^\d{10}$/',$date)) {
				# Unix Timestamp
				return date('Y-m-d H:i:s',$date);
			}
			elseif (isset($debug) && $debug) {
				error_log("Parsing regular date format $date");
			}

			# Regular Format (slash delimited)
			if (preg_match('/^(\d+)\/(\d+)\/(\d+)\s(\d+)\:(\d+)\:?(\d+)*/',$date,$matches)) {
				# mm/dd/yyyy hh:mm:ss
				$year = $matches[3];
				$month = $matches[1];
				$day = $matches[2];
				$hour = $matches[4];
				$minute = $matches[5];
				$second = $matches[6];
			}
			elseif (preg_match('/^(\d+)\/(\d+)\/(\d+)$/',$date,$matches)) {
				# mm/dd/yyyy
				$year = $matches[3];
				$month = $matches[1];
				$day = $matches[2];
				$hour = 0;
				$minute = 0;
				$second = 0;
			}
			elseif (preg_match('/^(\d+)\/(\d+)$/',$date,$matches)) {
				# mm/dd/yyyy
				$year = date('Y');
				$month = $matches[1];
				$day = $matches[2];
				$hour = 0;
				$minute = 0;
				$second = 0;
			}
			elseif (preg_match('/^(\d+)\/(\d+)\s(\d+)\:(\d+)\:?(\d+)*/',$date,$matches)) {
				# mm/dd hh:mm:ss
				$year = date('Y');
				$month = $matches[1];
				$day = $matches[2];
				$hour = $matches[3];
				$minute = $matches[4];
				$second = $matches[5];
			}

			# Default 0 Seconds
			if (! preg_match('/^\d+$/',$second)) $second = 0;

			# Partial Year
			if (strlen($year) < 3) $year = $year + 2000;
	
			if (checkdate($month,$day,$year)) {
				# Build new date string
				return sprintf("%04d-%02d-%02d %02d:%02d:%02d",$year,$month,$day,$hour,$minute,$second);
			}
			else {
				app_log("get_mysql_date generated an invalid date: '$month/$day/$year'",'error',__FILE__,__LINE__);
				return null;
			}

			# If Range Given, See if Date In Range
			if (($range > 0) and (strtotime($date) <= time())) {
				$date = "";
			}
			elseif (($range < 0) and (strtotime($date) >= time())) {
				$date = "";
			}

			# Return Formatted Date
			app_log("get_mysql_date returning $date",'debug');
			return $date;
		}

		// 16 Character UUID
		public function uuid() {
			$pid = substr($this->intToUUID(getmypid()),-3);
			$sec = uniqid();
			return strtoupper("$pid$sec");
		}

		public function biguuid() {
			$pid = $this->intToUUID(getmypid());
			$sec = uniqid();
			$rnd = $this->intToUUID(mt_rand(0,mt_getrandmax()));
			return strtoupper("$pid-$sec-$rnd");
		}

		public function intToUUID($int) {
			$string = '';
			while ($int / 36 >= 1) {
				$char = $int % 36;
				$int = sprintf("%0d",$int/36);

				if ($char < 11) $char = chr($char + 48);
				else $char = chr($char + 55);
				$string = $char . $string;
			}
			return $string;
		}

		/**
		 * Check if a string is a valid email address
		 * @param mixed $email Email Address
		 * @return bool True if valid email address
		 */
		function validEmail($email): bool {
			if (preg_match("/^[\w\-\_\.\+]+@[\w\-\_\.]+\.[a-z]{2,}$/",strtolower($email))) return true;
			else {
				app_log("Invalid email address: '$email'",'info');
				return false;
			}
		}

		/**
		 * Check if a date is valid
		 * Note: valid means it can be processed by get_mysql_date() in the include/function.php file
		 * @param mixed $date 
		 * @return bool True if valid date
		 */
		function validDate($date): bool {
			if (get_mysql_date($date)) return true;
			else {
				app_log("Invalid date format: '$date'",'info');
				return false;
			}
		}

		/**
		 * Check if a timezone is valid
		 * @param mixed $tz Time Zone String
		 * @return bool True if valid timezone
		 */
		function validTimeZone($tz): bool {
			if (in_array($tz,timezone_identifiers_list())) return true;
			else {
				app_log("Invalid timezone",'info');
				return false;
			}
		}

		/**
		 * Check if Slack Channel is a valid format
		 * @param string Slack Channel
		 * @return bool True if valid
		 */
		function validSlackChannel($string): bool {
			// Slack channels start with a # and can contain alphanumeric characters, underscores, and hyphens
			if (preg_match('/^#([a-zA-Z0-9_-]+)$/', $string)) return true;
			else {
				app_log("Invalid Slack channel format: '$string'",'info');
				return false;
			}
		}

		/** @method public validClassName(string)
		 * Validate a class name
		 * @param string $class_name The class name to validate
		 * @return bool True if valid class name
		 */
		public function validClassName(string $class_name): bool {
			if (preg_match('/^[A-Za-z_][A-Za-z0-9_\\\\]*$/',$class_name)) {
				if (class_exists($class_name)) return true;
				else return false;
			}
			else {
				app_log("Invalid class name: '$class_name'",'info');
				return false;
			}
		}
	}