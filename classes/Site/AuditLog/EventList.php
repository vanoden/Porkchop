<?php

namespace Site\AuditLog;

class EventList extends \BaseListClass {
    
    protected $database;

    public function __construct() {
        $this->_modelName = '\Site\AuditLog\Event';
        $this->_tableDefaultSortBy = 'date_event';
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

        if (isset($params['status'])) {
            if (is_array($params['status'])) {
                if (count($params['status']) > 0) {
                    $statii = "";
                    foreach ($params['status'] as $status) {
                        if (preg_match('/^\w+$/',$status)) {
                            if (strlen($statii) > 0) $statii .= ",";
                            $statii .= "'$status'";
                        }
                    }
                    $find_events_query .= "
                        AND class_method in (".$statii.")";
                }
                else {
                    $find_events_query .= "
                        AND id != id";
                }
            }
            elseif (!empty($params['status'])) {
                $find_events_query .= "
                    AND class_method = ?";
                array_push($bind_params, $params['status']);
            }
        }

        // apply the order and sort direction
        if (!empty($params['order_by']) && !empty($params['sort_direction'])) {
            $order_by_clause = " ORDER BY ";
            $sort_direction_clause = " `" . $params['sort_direction'] . "` " . strtoupper($params['order_by']);
            $find_events_query .= $order_by_clause . $sort_direction_clause;
		}

        $rs = $GLOBALS['_database']->Execute($find_events_query, $bind_params);
        if (!$rs) {
            $this->error("SQL Error in \\Site\\AuditLog\\EventList::find: " . $GLOBALS['_database']->ErrorMsg());
            return null;
        }

        $events = array();
        while (list($id) = $rs->FetchRow()) {
            app_log("Adding order $id");
            $event = new \Site\AuditLog\Event($id);
            array_push($events,$event);
            $this->_count ++;
        }
        return $events;
    }
}
