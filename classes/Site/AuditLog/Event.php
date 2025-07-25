<?php
namespace Site\AuditLog;

use Aws\TrustedAdvisor\TrustedAdvisorClient;

class Event Extends \BaseModel {
	public $event_date = null;			// Datetime of the event
	public ?int $user_id = null;		// ID of the customer triggering the event
	public ?int $instance_id = null;	// ID of the instance being audited
	public $class_name = null;			// Class name of the object being audited
	public $class_method = null;		// Method name of the object being audited
	public $description = null;			// Description of the event

	/**
	 * Constructor
	 * @param int $id 
	 * @return void 
	 */
	public function __construct($id = 0) {
		$this->_tableName = 'site_audit_events';
		$this->_addFields(array('id', 'event_date', 'user_id', 'instance_id', 'class_name', 'class_method', 'description'));
		parent::__construct($id);
	}

	/**
	 * Add an event to the audit log only if a description is provided
	 * @param array $params 
	 * @return bool 
	 */
	public function addIfDescription($params = []) {
		app_log("Shall we log?");
		if (empty($this->description)) return true;
		app_log("Yes, we shall log.");
		return $this->add($params);
	}

	/**
	 * Add an event to the audit log
	 * @param array $params 
	 * @return bool 
	 */
	public function add($params = []) {
		$this->clearError();

		// Create a new database object
		$database = new \Database\Service();

		// By default, do not log the event
		$log_this_event = false;

		// Get the calling class
		$callingClassName = $this->getCallingClass();
		if (empty($callingClassName)) {
			app_log("Calling Class is empty");
			app_log(print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4), true));
			return true;
		}

		if (! property_exists($callingClassName, '_auditEvents')) return true;

		// if no classes set to be audited, return true
		if (!empty($GLOBALS['_config']->auditing->auditedClasses) && is_array($GLOBALS['_config']->auditing->auditedClasses)) {
			// if the class_name is set in $params, check if it is in the auditedClasses array
			if (isset($params['class_name']) && in_array($params['class_name'], $GLOBALS['_config']->auditing->auditedClasses)) $log_this_event = true;
		}

		$database = new \Database\Service();
		if (empty($params['instance_id']) || empty($params['description'])) {
			$this->error("Instance ID and description are required.");
			return false;
		}
		if (empty($GLOBALS['_SESSION_']->customer->id)) {
			if ($_SERVER['SCRIPT_FILENAME'] == BASE."/core/install.php") {
				// Allow install.php to run without a customer ID
				return true;
			}
			app_log("Rejected audit event - No customer ID. Params: " . print_r($params, true));
			app_log(print_r($params, true));
			$this->error("No customer ID found in session.  Cannot log event.");
			return false;
		}

		$this->instance_id = $params['instance_id'];
		$this->class_name = !empty($params['class_name']) ? $params['class_name'] : $this->getCallingClass();
		$this->class_method = !empty($params['class_method']) ? $params['class_method'] : $this->getCallingMethod();
		if (!empty($params['description'])) $this->description = $params['description'];
		$this->event_date = date('Y-m-d H:i:s');
		$this->user_id = !empty($GLOBALS['_SESSION_']->customer->id) ? $GLOBALS['_SESSION_']->customer->id : 0;

		$query = "
			INSERT INTO site_audit_events
			(event_date, user_id, instance_id, class_name, class_method, description)
			VALUES (?, ?, ?, ?, ?, ?)
		";

		$database->AddParams(array(
			$this->event_date,
			$this->user_id,
			$this->instance_id,
			$this->class_name,
			$this->class_method,
			$this->description
		));

		$rs = $database->Execute($query);
		if (!$rs) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		$this->id = $database->Insert_ID();
		return true;
	}   

	protected function getCallingClass() {
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
		if ($backtrace[1]['class'] == 'Site\AuditLog\Event' || $backtrace[1]['class'] == 'BaseModel') {
			if ($backtrace[2]['class'] == 'Site\AuditLog\Event' || $backtrace[2]['class'] == 'BaseModel') {
				if (!empty($backtrace[3]['class'])) return $backtrace[3]['class'];
				return null;
			}
			if (!empty($backtrace[2]['class'])) return $backtrace[2]['class'];
			return null;
		}
		return isset($backtrace[1]['class']) ? $backtrace[1]['class'] : null;
	}

	protected function getCallingMethod() {
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
		if ($backtrace[1]['class'] == 'Site\AuditLog\Event' || $backtrace[1]['class'] == 'BaseModel') {
			if ($backtrace[2]['class'] == 'Site\AuditLog\Event' || $backtrace[2]['class'] == 'BaseModel') {
				if (!empty($backtrace[3]['function'])) return $backtrace[3]['function'];
				return null;
			}
			if (!empty($backtrace[2]['function'])) return $backtrace[2]['function'];
			return null;
		}
		return isset($backtrace[1]['function']) ? $backtrace[1]['function'] : null;
	}

	public function appendDescription($description) {
		if (!empty($this->description)) $this->description .= ', ';
		$this->description .= $description;
	}
}
