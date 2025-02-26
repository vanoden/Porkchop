<?php
/**
 * Base class providing common functionality for error handling, validation, and sanitization
 */
class BaseClass {

	// Error Message
	protected $_error;

	private $_warning;

	// Possible statuses in enum status table for validation (where applicable)
	protected $_statii = array();

	// Possible types in enum type table for validation (where applicable)
	protected $_types = array();

	/**
	 * Validation patterns for different input types
	 */
	protected $_patterns = [
		'phone' => '/[^0-9\+\-\(\)\s]/u',
		'address' => '/[^a-zA-Z0-9\s\-\.,#\']/u',
		'price' => '/[^0-9\.\,]/u',
		'percentage' => '/[^0-9\-\.%]/u',
		'datetime' => '/[^0-9\-\/\:\s]/u',
		'mac_address_format' => '/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
		'search' => '/^[\*\w\-\_\.\s]*$/',
		'address_line' => '/^[\w? :.-|\'\)]+$/',
		'city_name' => '/^[\w? :.-|\'\)]+$/',
		'code' => '/^\w[\w\-\.\_\s]*$/',
		'name' => '/\w[\w\-\.\_\s\,\!\?\(\)]*$/'
	];

	/********************************************/
	/* Reusable Error Handling Routines			*/
	/********************************************/
	/**
	 * Set or get the error message
	 * 
	 * @param string|null $value The error message to set, or null to get current error
	 * @param array|null $caller The caller information array, or null to get from debug_backtrace
	 * @return string|null The current error message
	 */
	public function error($value = null, $caller = null) {
		if (isset($value)) {
			if (!isset($caller)) {
				$trace = debug_backtrace();
				$caller = $trace[1];
			}
			$class = $caller['class'];
			$classname = str_replace('\\', '::', $class);
			$method = $caller['function'];
			$this->_error = $value;
			app_log(get_called_class() . "::" . $method . "(): " . $this->_error, 'error');
		}
		return $this->_error;
	}

	/****************************************/
	/* Recognize Special Error Types 		*/
	/****************************************/
	/**
	 * Determine the type of error based on the error message pattern
	 * 
	 * @return string|null The error type ('MySQL Unavailable', 'MySQL Query Error', 'Common') or null if no error
	 */
	public function errorType() {
		if (empty($this->_error)) return null;
		if (preg_match('/MySQL server has gone away/', $this->_error)) return 'MySQL Unavailable';
		if (preg_match('/Lost connection to MySQL server/', $this->_error)) return 'MySQL Unavailable';
		if (preg_match('/No database selected/', $this->_error)) return 'MySQL Unavailable';
		if (preg_match('/Table \'(\w+)\' doesn\'t exist/', $this->_error, $matches)) return 'MySQL Query Errord';
		if (preg_match('/Unknown column \'(\w+)\' in \'(\w+)\'/', $this->_error, $matches)) return 'MySQL Query Error';
		if (preg_match('/Duplicate entry \'(\w+)\' for key \'(\w+)\'/', $this->_error, $matches)) return 'MySQL Query Error';
		return 'Common';
	}

	/**
	 * Set or get the warning message
	 * 
	 * @param string|null $value The warning message to set, or null to get current warning
	 * @param array|null $caller The caller information array, or null to get from debug_backtrace
	 * @return string|null The current warning message
	 */
	public function warn($value = null, $caller = null) {
		if (isset($value)) {
			if (!isset($caller)) {
				$trace = debug_backtrace();
				$caller = $trace[1];
			}
			$class = $caller['class'];
			$classname = str_replace('\\', '::', $class);
			$method = $caller['function'];
			$this->_warning = $value;
			app_log(get_called_class() . "::" . $method . "(): " . $this->_warning, 'warn');
		}
		return $this->_warning;
	}

	/**
	 * Get the object name from the caller class
	 * 
	 * @return string The extracted class name or "Object" if not found
	 */
	public function _objectName() {
		if (!isset($caller)) {
			$trace = debug_backtrace();
			$caller = $trace[2];
		}

		$class = isset($caller['class']) ? $caller['class'] : null;
		if (preg_match('/(\w[\w\_]*)$/', $class, $matches)) $classname = $matches[1];
		else $classname = "Object";
		return $classname;
	}

	/********************************************/
	/* SQL Errors - Identified and Formatted	*/
	/* for filtering and reporting				*/
	/********************************************/
	/**
	 * Handle SQL errors with proper formatting and logging
	 * 
	 * @param string $message The error message, empty to get from global database
	 * @param string|null $query The SQL query that caused the error
	 * @param array|null $bind_params The bind parameters used in the query
	 * @return string The formatted error message
	 */
	public function SQLError($message = '', $query = null, $bind_params = null) {
		if (empty($message)) $message = $GLOBALS['_database']->ErrorMsg();
		$trace = debug_backtrace();
		$caller = $trace[1];
		$class = $caller['class'];
		$classname = str_replace('\\', '::', $class);
		$method = $caller['function'];
		if (!empty($query)) query_log($query, $bind_params, true);
		return $this->error("SQL Error in " . $classname . "::" . $method . "(): " . $message, $caller);
	}

	/**
	 * Clear the current error message
	 */
	public function clearError() {
		$this->_error = null;
	}

	/**
	 * Get the list of valid types
	 * 
	 * @return array Array of valid types
	 */
	public function types() {
		return $this->_types;
	}

	/**
	 * Get the list of valid statuses
	 * 
	 * @return array Array of valid statuses
	 */
	public function statuses() {
		return $this->_statii;
	}

	/********************************************/
	/* Reusable Validation Routines				*/
	/********************************************/
	/**
	 * Validate a code string against the code pattern
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validCode($string): bool {
		return (preg_match($this->_patterns['code'], $string));
	}

	/**
	 * Sanitize a value based on a specified type
	 * 
	 * @param mixed $value The value to sanitize
	 * @param string $type The type of sanitization to apply
	 * @return mixed The sanitized value
	 */
	public function sanitize($value, string $type): mixed {
		if (!isset($this->_patterns[$type]) && !in_array($type, ['text', 'alpha', 'alphanumeric', 'email', 'website', 'integer', 'decimal', 'ip_address', 'mac_address', 'filename', 'path', 'username', 'password'])) {
			$this->error("Unknown type '{$type}' for sanitization");
			return $value;
		}

		if (is_array($value)) {
			foreach ($value as $key => $val) $value[$key] = $this->sanitize($val, $type);
			return $value;
		}

		if (!is_string($value)) return $value;

		// Special pre-processing
		$value = trim($value);
		switch ($type) {
			case 'email':
				$value = strtolower($value);
				break;
			case 'price':
				$value = str_replace(',', '', $value);
				break;
			case 'percentage':
				$value = str_replace('%', '', $value);
				break;
		}

		// Apply validation based on type
		switch ($type) {
			case 'text':
				return ctype_print($value) ? $value : '';
			case 'alpha':
				return ctype_alpha($value) ? $value : '';
			case 'alphanumeric':
				return ctype_alnum($value) ? $value : '';
			case 'integer':
				return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
			case 'decimal':
				return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			case 'email':
				return filter_var($value, FILTER_SANITIZE_EMAIL);
			case 'website':
				return filter_var($value, FILTER_SANITIZE_URL);
			case 'ip_address':
				return filter_var($value, FILTER_VALIDATE_IP) ? $value : '';
			case 'filename':
			case 'path':
			case 'username':
			case 'password':
				return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
			default:
				// Use existing regex patterns for remaining types
				return preg_replace($this->_patterns[$type], '', $value);
		}
	}

	/**
	 * Clean data to prevent XSS attacks
	 * 
	 * @param mixed $data The data to clean
	 * @return mixed The cleaned data
	 */
	protected function cleanXSS($data): mixed {
		if (is_array($data)) {
			foreach ($data as $key => $value) $data[$key] = $this->cleanXSS($value);
			return $data;
		}

		if (is_string($data)) {
			return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
		}

		return $data;
	}

	/**
	 * Validate a name string against the name pattern
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validName($string): bool {
		return (preg_match($this->_patterns['name'], $string));
	}

	/**
	 * Validate a status string against the allowed statuses
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validStatus($string): bool {
		return (in_array($string, $this->_statii));
	}

	/**
	 * Validate a type string against the allowed types
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validType($string): bool {
		return (in_array($string, $this->_types));
	}

	/**
	 * Validate a search string against the search pattern
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validSearch($string): bool {
		return (preg_match($this->_patterns['search'], $string));
	}

	/**
	 * Validate an address line against the address line pattern
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validAddressLine($string): bool {
		return (preg_match($this->_patterns['address_line'], urldecode($string)));
	}

	/**
	 * Validate a city name against the city name pattern
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validCity($string): bool {
		return (preg_match($this->_patterns['city_name'], urldecode($string)));
	}

	/**
	 * Validate a hostname against the hostname pattern
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validHostname($string): bool {
		return (preg_match($this->_patterns['hostname'], $string));
	}

	/**
	 * Validate a URL string against the URL format pattern
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validURL($string): bool {
		define(
			'URL_FORMAT',
			'/^(https?):\/\/' .                                         // protocol
				'(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+' .         // username
				'(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?' .      // password
				'@)?(?#' .                                                  // auth requires @
				')((([a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*' .           // domain segments AND
				'[a-z][a-z0-9-]*[a-z0-9]' .                                 // top level domain  OR
				'|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}' .
				'(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])' .                 // IP address
				')(:\d+)?' .                                                // port
				')(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*' . // path
				'(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)' .      // query string
				'?)?)?' .                                                   // path and query string optional
				'(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?' .      // fragment
				'$/i'
		);
		return (preg_match(URL_FORMAT, $string));
	}

	/**
	 * Validate a text string using ctype_print
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validText($string): bool {
		return ctype_print($string);
	}

	/**
	 * Validate an alphabetic string using ctype_alpha
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validAlpha($string): bool {
		return ctype_alpha($string);
	}

	/**
	 * Validate an alphanumeric string using ctype_alnum
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validAlphanumeric($string): bool {
		return ctype_alnum($string);
	}

	/**
	 * Validate a phone number against the phone pattern
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validPhone($string): bool {
		return !preg_match($this->_patterns['phone'], $string);
	}

	/**
	 * Validate an email address using PHP's filter_var
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validEmail($string): bool {
		return filter_var($string, FILTER_VALIDATE_EMAIL) !== false;
	}

	/**
	 * Validate a website URL using PHP's filter_var
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validWebsite($string): bool {
		return filter_var($string, FILTER_VALIDATE_URL) !== false;
	}

	/**
	 * Validate an address against the address pattern
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validAddress($string): bool {
		return !preg_match($this->_patterns['address'], $string);
	}

	/**
	 * Validate an integer using PHP's filter_var
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validInteger($string): bool {
		return is_numeric($string) && filter_var($string, FILTER_VALIDATE_INT) !== false;
	}

	/**
	 * Validate a decimal number using PHP's filter_var
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validDecimal($string): bool {
		return is_numeric($string) && filter_var($string, FILTER_VALIDATE_FLOAT) !== false;
	}

	/**
	 * Validate a price against the price pattern
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validPrice($string): bool {
		$string = str_replace(',', '', $string);
		return !preg_match($this->_patterns['price'], $string);
	}

	/**
	 * Validate a percentage against the percentage pattern
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validPercentage($string): bool {
		$string = str_replace('%', '', $string);
		return !preg_match($this->_patterns['percentage'], $string);
	}

	/**
	 * Validate a date string using PHP's date_parse
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validDate($string): bool {
		if (preg_match($this->_patterns['date'], $string)) return false;
		$date = date_parse($string);
		return $date['error_count'] === 0;
	}

	/**
	 * Validate a time string using PHP's date_parse
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validTime($string): bool {
		if (preg_match($this->_patterns['time'], $string)) return false;
		$time = date_parse($string);
		return $time['error_count'] === 0;
	}

	/**
	 * Validate a datetime string using PHP's date_parse
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validDatetime($string): bool {
		if (preg_match($this->_patterns['datetime'], $string)) return false;
		$datetime = date_parse($string);
		return $datetime['error_count'] === 0;
	}

	/**
	 * Validate a username against the username pattern
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validUsername($string): bool {
		return !preg_match($this->_patterns['username'], $string);
	}

	/**
	 * Validate an IP address using PHP's filter_var
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validIPAddress($string): bool {
		return filter_var($string, FILTER_VALIDATE_IP) !== false;
	}

	/**
	 * Validate a MAC address using PHP's filter_var
	 * 
	 * @param string $string The string to validate
	 * @return bool True if valid, false otherwise
	 */
	public function validMACAddress($string): bool {
		return filter_var($string, FILTER_VALIDATE_MAC) !== false;
	}

	/**
	 * Validate a string for potential security threats
	 * 
	 * @param string $string The string to validate
	 * @return bool True if safe, false otherwise
	 */
	public function safeString($string): bool {
		
		$string = urldecode($string);
		if (preg_match('/(&#*\w+)[\x00-\x20]+;/u', $string)) return false;
		if (preg_match('/(&#x*[0-9A-F]+);*/iu', $string)) return false;

		if (preg_match('/(\<|&lt;)\s*(script|iframe|object|embed|applet|meta|link)/i', $string)) return false;

		// javascript protocol
		if (preg_match('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', $string)) return false;
		// vbscript protocol
		if (preg_match('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', $string)) return false;
		// mozbinding protocol
		if (preg_match('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', $string)) return false;
		// Attribute starting with "on" or "data" or "xmlns"
		if (preg_match('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns|data)[^>]*+>#iu', $string)) return false;
		// Non alpha-numeric characters in attribute names
		if (preg_match('/[^\w\"\']=/', $string)) return false;
		// javascript: in inline events
		if (preg_match('/alert\(/i', $string)) return false;

		return (preg_match('/^[^\%\<\>]+$/', $string));
	}

	/**
	 * Create a clone of the object without protected properties
	 * 
	 * @return stdClass A new object containing only public properties
	 */
	public function _clone() {
		$obj = new \stdClass();
		foreach (get_object_vars($this) as $key => $value) {
			if (preg_match('/^_/', $key)) continue;
			$obj->$key = $value;
		}
		return $obj;
	}

	/**
	 * Get the current error message
	 * 
	 * @return string|null The current error message
	 */
	public function getError() {
		return $this->_error;
	}
}
