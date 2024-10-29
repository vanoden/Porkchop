<?php

namespace Site\AuditLog;

class EventList extends \BaseListClass {
	public function __construct() {
		$this->_modelName = '\Site\AuditLog\Event';
		$this->_tableDefaultSortBy = 'date_event';
	}

	public function findAdvanced($parameters, $advanced, $controls): array {
		$this->clearError();
		$this->resetCount();

		// Initialize Database Service
		$database = new \Database\Service();

		// Dereference Working Class
		$workingClass = new $this->_modelName;

		$customer_id = isset($params['customer_id']) ? $params['customer_id'] : null;
		$class_name = isset($params['class_name']) ? $params['class_name'] : null;
		$instance_id = isset($params['instance_id']) ? $params['instance_id'] : null;
		$change_type = isset($params['change_type']) ? $params['change_type'] : null;
		$description = isset($params['description']) ? $params['description'] : null;

		// Build Query
		$find_objects_query = "
			SELECT 	`".$workingClass->_tableIdColumn()."`
			FROM	`".$workingClass->_tableName()."`
			WHERE	`".$workingClass->_tableIdColumn()."` = `".$workingClass->_tableIdColumn()."`
		";

		// Add Parameters
		if (!empty($parameters['customer_id']) && is_numeric($parameters['customer_id'])) {
			$customer = new \Register\Customer($parameters['customer_id']);
			if ($customer->exists()) {
				$find_objects_query .= " AND user_id = ?";
				$database->AddParam($parameters['customer_id']);
			}
			else {
				$this->error("Customer not found");
				return [];
			}
		}

		if (!empty($parameters['class_name'])) {
			if (class_exists($workingClass->_modelName)) {
				$find_objects_query .= " AND class_name = ?";
				$database->AddParam($parameters['class_name']);
			}
			else {
				$this->error("Invalid class name");
				return [];
			}
		}

		if (!empty($parameters['instance_id']) && is_numeric($parameters['instance_id'])) {
			$find_objects_query .= " AND instance_id = ?";
			$database->AddParam($parameters['instance_id']);
		}

		if (!empty($parameters['change_type'])) {
			$find_objects_query .= " AND class_method = ?";
			$database->AddParam($parameters,'change_type');
		}

		if (!empty($parameters['description'])) {
			if ($workingClass->validSearchString($parameters['description'])) {
				$find_objects_query .= " AND description LIKE ?";
				$database->AddParam('%' . $parameters['description'] . '%');
			}
			else {
				$this->error("Invalid description");
				return [];
			}
		}
/*
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
*/
		// apply the order and sort direction
		if (!empty($controls['sort']) && !empty($controls['order'])) {
			$order_by_clause = " ORDER BY ";
			$sort_direction_clause = " `" . $controls['sort'] . "` " . strtoupper($controls['order']);
			$find_objects_query .= $order_by_clause . $sort_direction_clause;
		}

		// Limit Clause
		$find_objects_query .= $this->limitClause($controls);

		// Execute Query
		$rs = $database->Execute($find_objects_query);
		if (!$rs) {
			$this->SQLError($database->ErrorMsg());
			return [];
		}

		// Assemble Results
		$objects = array();
		while (list($id) = $rs->FetchRow()) {
			$object = new $this->_modelName($id);
			array_push($objects,$object);
			$this->incrementCount();
		}
		return $objects;
	}
}
