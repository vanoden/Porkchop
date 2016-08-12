<?
	###################################################
	### Make Sure MySQL Date is Valid               ###
	###################################################
	function require_module($module,$view='default') {
		require_once(MODULES."/".$module."/_classes/$view.php");
	}
	function get_mysql_date($date,$range=0) {
		# Handle Some Keywords
		if (preg_match("/today/i",$date)) return date("Y-m-d");
		if (preg_match("/now/i",$date)) return date("Y-m-d h:i:s");
		if (preg_match("/tomorrow/i",$date)) return date("Y-m-d",time() + 86400);

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
		elseif ($debug) {
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
	
	function cache_set($key,$value,$expires=0) {
		if (! isset($GLOBALS['_config']->cache_mechanism)) return null;
		if ($GLOBALS['_config']->cache_mechanism == 'xcache') {
			$value = serialize($value);
			return xcache_set($key,$value);
		}
		elseif ($GLOBALS['_config']->cache_mechanism == 'memcache') {
			$result = $GLOBALS['_memcache']->set($key,$value,0,$expires);
			if (! $result) {
				app_log("Error setting cache: ".$GLOBALS['_config']->getResultCode(),'error',__FILE__,__LINE__);
			}
			return $result;
		}
		elseif ($GLOBALS['_config']->cache_mechanism == 'file') {
			if (is_dir($GLOBALS['_config']->cache_path) || mkdir($GLOBALS['_config']->cache_path,0700,true)) {
				if ($fh = fopen($GLOBALS['_config']->cache_path."/".$key,'w')) {
					$value = serialize($value);
					fwrite($fh,$value);
					fclose($fh);
				}
				else {
					app_log("Cannot create cache file",'error',__FILE__,__LINE__);
				}
			}
			else {
				app_log("Cannot create cache path",'error',__FILE__,__LINE__);
			}
		}
		else {
			app_log("No cache mechanism available");
		}
		return null;
	}
	function cache_unset($key) {
		if (! isset($GLOBALS['_config']->cache_mechanism)) return null;
		
		if ($GLOBALS['_config']->cache_mechanism == 'xcache') {
			return xcache_unset($key);
		}
		elseif ($GLOBALS['_config']->cache_mechanism == 'memcache') {
			return $GLOBALS['_memcache']->delete($key);
		}
		elseif ($GLOBALS['_config']->cache_mechanism == 'file') {
			if (is_dir($GLOBALS['_config']->cache_path)) {
				$filename = $GLOBALS['_config']->cache_path."/".$key;
				unlink($filename);
			}
		}
		return null;
	}
	function cache_get($key) {
		if (! isset($GLOBALS['_config']->cache_mechanism)) return null;
		if ($GLOBALS['_config']->cache_mechanism == 'xcache') {
			if (xcache_isset($key)) {
				return unserialize(xcache_get($key));
			}
		}
		elseif ($GLOBALS['_config']->cache_mechanism == 'memcache') {
			return $GLOBALS['_memcache']->get($key);
		}
		elseif ($GLOBALS['_config']->cache_mechanism == 'file') {
			if (is_dir($GLOBALS['_config']->cache_path)) {
				$filename = $GLOBALS['_config']->cache_path."/".$key;
				if (! file_exists($filename)) {
					app_log("No cache available",'debug',__FILE__,__LINE__);
					return null;
				}
				if ($fh = fopen($filename,'r')) {
					$content = fread($fh,filesize($filename));
					$value = unserialize($content);
					fclose($fh);
					return $value;
				}
				else {
					app_log("Cannot open cache file '$filename'",'error',__FILE__,__LINE__);
				}
			}
			else {
				app_log("Cannot access cache path",'error',__FILE__,__LINE__);
			}
		}
		return null;
	}

	# API Logging (With actual parameters)
	function api_log($response = 'N/A') {
		$log = "";
		$module = $GLOBALS['_REQUEST_']->module;
		$login = $GLOBALS['_SESSION_']->customer->code;
		$method = $_REQUEST['method'];
		$host = $GLOBALS['_REQUEST_']->client_ip;

		if ($response->success) $status = "SUCCESS";
		else $status = "FAILED";
		$elapsed = microtime() - $GLOBALS['_REQUEST_']->timer;

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
	function app_log($message, $level = 'info', $path = 'unknown', $line = 'unknown') {
		# PHP Syslog Levels (also for level validation)
		$syslog_xref = array(
			"emergency" => LOG_EMERG,
			"alert"		=> LOG_ALERT,
			"critical"	=> LOG_CRIT,
			"error"		=> LOG_ERR,
			"warning"	=> LOG_WARNING,
			"notice"	=> LOG_NOTICE,
			"info"		=> LOG_INFO,
			"debug"		=> LOG_DEBUG
		);

		# Make Sure Severity Level is Valid
		$level = strtolower($level);
		if (! array_key_exists($level,$syslog_xref)) $level = "info";

		# Filter on log level
		if (APPLICATION_LOG_LEVEL == "error" && in_array($level,array('debug','info','notice','warning'))) return null;
		elseif (APPLICATION_LOG_LEVEL == "warning" && in_array($level,array('debug','info','notice'))) return null;
		elseif (APPLICATION_LOG_LEVEL == "notice" && in_array($level,array('debug','info'))) return null;
		elseif (APPLICATION_LOG_LEVEL == "info" && $level == "debug") return null;

		# Replace Carriage Returns
		$message = preg_replace('/\r*\n/',"\n",$message);

		# Prepare Values for String
		$date = date('Y/m/d H:i:s');
		$path = preg_replace('#^'.BASE.'/#','',$path);

		if ((array_key_exists('_page',$GLOBALS)) and (property_exists($GLOBALS['_page'],'module'))) $module = $GLOBALS['_page']->module;
		else $module = '-';
		if ((array_key_exists('_page',$GLOBALS)) and (property_exists($GLOBALS['_page'],'view'))) $view = $GLOBALS['_page']->view;
		else $view = '-';
		if (array_key_exists('_SESSION_',$GLOBALS)) {
			if (property_exists($GLOBALS['_SESSION_'],'id')) $session_id = $GLOBALS['_SESSION_']->id;
			else $session_id = '-';
			if (property_exists($GLOBALS['_SESSION_'],'customer')) $customer_id = $GLOBALS['_SESSION_']->customer->id;
			else $customer_id = '-';
		}
		else {
			$session_id = '-';
			$customer_id = '-';
		}

		# Build String
		$string = "$date [$level] $module::$view $path:$line $session_id $customer_id $message\n";

		# Send to appropriate log
		if (preg_match('/^syslog$/i',APPLICATION_LOG)) {
			syslog($syslog_xref[$level],$string);
		}
		elseif (is_dir(APPLICATION_LOG)) {
			$log = fopen(APPLICATION_LOG."/application_log",'a');
			fwrite($log,$string);
			fclose($log);
		}
		elseif(APPLICATION_LOG) {
			$log = fopen(APPLICATION_LOG,'a');
			fwrite($log,$string);
			fclose($log);
		}
		else {
			error_log($message);
		}
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
		if (isset($GLOBALS['_SESSION_']->customer) and (is_array($GLOBALS['_SESSION_']->customer->roles)) and (in_array($role,$GLOBALS['_SESSION_']->customer->roles)))
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
?>
