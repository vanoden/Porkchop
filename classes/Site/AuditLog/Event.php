<?php
namespace Site\AuditLog;

class Event {
    
    public $id;
    public $event_date;
    public $user_id;
    public $instance_id;
    public $class_name;
    public $class_method;
    public $description;

    public function add($params = []) {

        if (empty($params['instance_id']) || empty($params['description'])) {
            $this->error = "Instance ID and description are required.";
            return false;
        }

        $this->instance_id = $params['instance_id'];
        $this->class_name = !empty($params['class_name']) ? $params['class_name'] : $this->getCallingClass();
        $this->class_method = !empty($params['class_method']) ? $params['class_method'] : $this->getCallingMethod();
        $this->description = $params['description'];
        $this->event_date = date('Y-m-d H:i:s');
        $this->user_id = !empty($GLOBALS['_SESSION_']->customer->id) ? $GLOBALS['_SESSION_']->customer->id : null;

        $query = "
            INSERT INTO site_audit_events
            (event_date, user_id, instance_id, class_name, class_method, description)
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $bind_params = [
            $this->event_date,
            $this->user_id,
            $this->instance_id,
            $this->class_name,
            $this->class_method,
            $this->description
        ];

        $rs = $this->database->Execute($query, $bind_params);
        if (!$rs) {
            $this->error = "SQL Error in Site\\AuditLog\\Event::add: " . $this->database->ErrorMsg();
            return false;
        }

        $this->id = $this->database->Insert_ID();
        return true;
    }   

    protected function getCallingClass() {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        return isset($backtrace[1]['class']) ? $backtrace[1]['class'] : null;
    }

    protected function getCallingMethod() {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        return isset($backtrace[1]['function']) ? $backtrace[1]['function'] : null;
    }
}