<?php
	###################################################
	### Make Sure MySQL Date is Valid               ###
	###################################################
	function require_module($module,$view='default') {
			error_log(print_r(debug_backtrace(),true));
		if (! require_once(MODULES."/".$module."/_classes/$view.php")) {
		}
	}
	function load_class($identifier) {
		$path = CLASS_PATH."/".preg_replace('/\\\\/','/',$identifier).".php";
		//app_log("Autoloading '$identifier' from '$path'",'debug',__FILE__,__LINE__);
		if (file_exists($path)) {
			require_once($path);
		}
		else {
			app_log("Porkchop autoloader found no class at '$path'",'error',__FILE__,__LINE__);
		}
	}
	function _debug_print($message) {
		error_log("DEBUG: ".$message);
	}

	function strongPassword($string) {
		$customer = new \Register\Customer();
		if (!isset($GLOBALS['_config']->register->minimum_password_strength)) return true;
		if ($customer->password_strength($string) > $GLOBALS['_config']->register->minimum_password_strength) return true;
		return false;
	}

	function valid_email($email) {
		if (preg_match("/^[\w\-\_\.\+]+@[\w\-\_\.]+\.[a-z]{2,}$/",strtolower($email))) return true;
		else {
			app_log("Invalid email address: '$email'",'info');
			return false;
		}
	}

	function get_mysql_date($date = null,$range=0) {
		if (empty($date)) {
			$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1];
			$ident = $caller['class']."::".$caller['function']." (".$caller['file'].":".$caller['line'].")";
			app_log("get_mysql_date() received empty date from $ident",'info');
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

	function shortDate($date) {
		if (preg_match('/^(\d\d\d\d)\-(\d\d)\-(\d\d)\s(\d\d)\:(\d\d)\:(\d\d)/',$date,$matches)) {
			list($junk,$year,$month,$day,$hours,$minutes,$seconds) = $matches;
			switch($month) {
				case 1:
					$month = 'Jan';
					break;
				case 2:
					$month = 'Feb';
					break;
				case 3:
					$month = 'Mar';
					break;
				case 4:
					$month = 'Apr';
					break;
				case 5:
					$month = 'May';
					break;
				case 6:
					$month = 'Jun';
					break;
				case 7:
					$month = 'Jul';
					break;
				case 8:
					$month = 'Aug';
					break;
				case 9:
					$month = 'Sep';
					break;
				case 10:
					$month = 'Oct';
					break;
				case 11:
					$month = 'Nov';
					break;
				case 12:
					$month = 'Dec';
					break;
			}

			if ($year == date('Y')) {
					return sprintf("%s %d %02d:%02d",$month,$day,$hours,$minutes);
			}
			else {
					return sprintf("%s %d %02d",$month,$day,$year);
			}
		}
		else {
			return $date;
		}
	}

	function cache_set($key,$value,$expires=0) {
		$cache = new \Cache\Item($GLOBALS['_CACHE_'],$key);
		return $cache->set($value);
	}
	function cache_unset($key) {
		$cache = new \Cache\Item($GLOBALS['_CACHE_'],$key);
		return $cache->delete();
	}
	function cache_get($key) {
		$cache = new \Cache\Item($GLOBALS['_CACHE_'],$key);
		return $cache->get();
	}

	# API Logging (With actual parameters)
	function api_log($response = 'N/A') {
		$log = "";
		$login = $GLOBALS['_SESSION_']->customer->code;
		if (!empty($_REQUEST['method'])) $method = $_REQUEST['method'];
		else $method = "[none]";

		if (is_object($response) && $response->success) $status = "SUCCESS";
		else $status = "FAILED";

		$module = $GLOBALS['_REQUEST_']->module;
		$host = $GLOBALS['_REQUEST_']->client_ip;
		if (is_numeric($GLOBALS['_REQUEST_']->timer)) $elapsed = microtime() - $GLOBALS['_REQUEST_']->timer;
		else $elapsed = 0;

		if (API_LOG) {
			if (is_dir(API_LOG))
				$log = fopen(API_LOG."/".$module.".log",'a');
			else 
				$log = fopen(API_LOG,'a');

			fwrite($log,"[".date('m/d/Y H:i:s')."] $host $module $login $method $status $elapsed\n");
			fwrite($log,"_REQUEST: ".print_r($_REQUEST,true));
			fwrite($log,"_RESPONSE: ".print_r($response,true));
			fclose($log);
		}
	}

	# Application Logging (leave error_log for system errors)
	function app_log($message, $level = 'debug', $path = null, $line = null) {
		if (! isset($path)) {
			$trace = debug_backtrace();
			$caller = $trace[0];
			$path = $caller['file'];
			$line = $caller['line'];
		}
		$GLOBALS['logger']->writeln($message,$level,$path,$line);
	}
	
	function executeSQLByParams($query, $bindParams) {
        $queryTime = microtime(true);
		$resultSet = $GLOBALS['_database']->Execute($query, $bindParams);
        query_log_time(microtime(true)-$queryTime, $query, $bindParams);
        return $resultSet;
	}
	
	function query_log_time($timeMilliseconds, $query,$params = array()) {
		$level = 'trace';
		app_log('Query Time Audit: ' . $timeMilliseconds . 'ms '.$query."\n".print_r($params,true),$level);
	}
	
	function query_log($query,$params = array(),$path = null,$line = null) {
		if (is_bool($path)) {
			$level = 'debug';
			$path = null;
		}
		else {
			$level = 'trace';
		}
		if (! isset($path)) {
			$trace = debug_backtrace();
			$caller = $trace[0];
			$path = $caller['file'];
			$line = $caller['line'];
		}
		app_log($query."\n".print_r($params,true),$level,$path,$line);
	}

	###############################################
	### Return Appropriate Error Message for	###
	### JSON parsing							###
	###############################################
	if (!function_exists('json_last_error_msg')) {
		function json_last_error_msg() {
			switch (json_last_error()) {
				default:
					return;
				case JSON_ERROR_DEPTH:
					$error = 'Maximum stack depth exceeded';
				break;
				case JSON_ERROR_STATE_MISMATCH:
					$error = 'Underflow or the modes mismatch';
				break;
				case JSON_ERROR_CTRL_CHAR:
					$error = 'Unexpected control character found';
				break;
				case JSON_ERROR_SYNTAX:
					$error = 'Syntax error, malformed JSON';
				break;
				case JSON_ERROR_UTF8:
					$error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
			}
			return $error;
		}
	}

	###################################################
	### Convert Object to XML						###
	###################################################
	function xml_encode($object,$user_options = '') {
		require 'XML/Unserializer.php';
		require 'XML/Serializer.php';
		$options = array(
			XML_SERIALIZER_OPTION_INDENT        => '    ',
			XML_SERIALIZER_OPTION_RETURN_RESULT => true,
			XML_SERIALIZER_OPTION_MODE			=> 'simplexml',
		);
		if ($user_options["rootname"]) {
			$options["rootName"] = $user_options["rootname"];
		}
		$xml = new XML_Serializer($options);
		if ($xml->serialize($object)) {
			//error_log("Returning ".$xml->getSerializedData());
			$output = $xml->getSerializedData();
			if ($user_options["stylesheet"]) {
				$output = "<?xml-stylesheet type=\"text/xsl\" href=\"/".$user_options["stylesheet"]."\"?>".$output;
			}
			return $output;
		}
	}

	###################################################
	### Check Permissions							###
	###################################################
	function role($role) {
		if (isset($GLOBALS['_SESSION_']->customer) and $GLOBALS['_SESSION_']->customer->has_role($role))
			return 1;
		else
			return 0;
	}
	###################################################
	### Format Query for Logging					###
	###################################################
	function format_query($string) {
		return preg_replace('/(\r\n)/','',preg_replace('/\t/',' ',$string));
	}

	# Cleanup special chars in output to avoid XSS
	function sanitize(&$string) {
		$string = trim($string);
		$string = htmlspecialchars($string);
		return $string;
	}

	# Trim Hexidecimal and HTML/XML tags from input
	function noXSS($string) {
		$string = preg_replace('/\%[a-f0-9]{2}/','',$string);
		$string = preg_replace('/(\<|\>)/','',$string);
		return $string;
	}

	# Check for basic token-like string
	function safeToken($string) {
		if (preg_match('/^[\w\-\.\_\s]{1,32}$/',$string)) return true;
		return false;
	}

	function validTimezone($string) {
		if (in_array($string, DateTimeZone::listIdentifiers())) return true;
		return false;
	}

	function prettyPrint( $json ) {
		$result = '';
		$level = 0;
		$in_quotes = false;
		$in_escape = false;
		$ends_line_level = NULL;
		$json_length = strlen( $json );
	
		for( $i = 0; $i < $json_length; $i++ ) {
			$char = $json[$i];
			$new_line_level = NULL;
			$post = "";
			if( $ends_line_level !== NULL ) {
				$new_line_level = $ends_line_level;
				$ends_line_level = NULL;
			}
			if ( $in_escape ) {
				$in_escape = false;
			} else if( $char === '"' ) {
				$in_quotes = !$in_quotes;
			} else if( ! $in_quotes ) {
				switch( $char ) {
					case '}': case ']':
						$level--;
						$ends_line_level = NULL;
						$new_line_level = $level;
						break;
	
					case '{': case '[':
						$level++;
					case ',':
						$ends_line_level = $level;
						break;
	
					case ':':
						$post = " ";
						break;
	
					case " ": case "\t": case "\n": case "\r":
						$char = "";
						$ends_line_level = $new_line_level;
						$new_line_level = NULL;
						break;
				}
			} else if ( $char === '\\' ) {
				$in_escape = true;
			}
			if( $new_line_level !== NULL ) {
				$result .= '\n'.str_repeat( '    ', $new_line_level );
			}
			$result .= $char.$post;
		}
	
		return $result;
	}

	function guess_mime_type($string) {
		if (preg_match('/\.(\w+)$/',$string,$matches)) {
			$extension = $matches[1];
			switch($extension) {
				case "csv":
					return "text/csv";
					break;
				case "gpg":
					return "application/pgp-encrypted";
					break;
				case "gz":
					return "application/gzip";
					break;
				case "html":
					return "text/html";
					break;
				case "jpg":
					return "image/jpeg";
					break;
				case "png":
					return "image/png";
					break;
				case "tif":
					return "image/tiff";
					break;
				case "tgz":
					return "application/tar+gzip";
					break;
				case "txt":
					return "text/plain";
					break;
                case "md5":
                    return "text/plain";
                    break;
				default:
					return null;
			}
		}
	}
?>
