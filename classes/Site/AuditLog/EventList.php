<?php

namespace Site\AuditLog;

class EventList extends \BaseListClass {
    
    protected $database;

    public function __construct() {
        $this->database = new \Database();
        if ($this->database->error) {
            $this->error = $this->database->error;
            return null;
        }
    }

    public function find($params = []) {
        $customer_id = isset($params['customer_id']) ? $params['customer_id'] : null;
        $class_name = isset($params['class_name']) ? $params['class_name'] : null;
        $instance_id = isset($params['instance_id']) ? $params['instance_id'] : null;
        $change_type = isset($params['change_type']) ? $params['change_type'] : null;
        $description = isset($params['description']) ? $params['description'] : null;

        $find_events_query = "
            SELECT id
            FROM site_audit_events
            WHERE 1
        ";

        $bind_params = [];

        if ($customer_id !== null) {
            $find_events_query .= " AND user_id = ?";
            $bind_params[] = $customer_id;
        }

        if ($class_name !== null) {
            $find_events_query .= " AND class_name = ?";
            $bind_params[] = $class_name;
        }

        if ($instance_id !== null) {
            $find_events_query .= " AND instance_id = ?";
            $bind_params[] = $instance_id;
        }

        if ($change_type !== null) {
            $find_events_query .= " AND class_method = ?";
            $bind_params[] = $change_type;
        }

        if ($description !== null) {
            $find_events_query .= " AND description LIKE ?";
            $bind_params[] = '%' . $description . '%';
        }

        $find_events_query .= " ORDER BY event_date DESC";

        $rs = $this->database->Execute($find_events_query, $bind_params);
        if (!$rs) {
            $this->error = "SQL Error in \\Site\\AuditLog\\EventList::find: " . $this->database->ErrorMsg();
            return null;
        }

        $events = [];
        while (list($id) = $rs->FetchRow()) {
            $event = new Event($id);
            $events[] = $event;
            $this->incrementCount();
        }

        return $events;
    }
}
