<?php

namespace Site\AuditLog;

class EventList extends \BaseListClass {
	public function __construct() {
		$this->_modelName = '\Site\AuditLog\Event';
		$this->_tableDefaultSortBy = 'event_date';
	}

	public function findAdvanced($parameters, $advanced, $controls): array {
		$this->clearError();
		$this->resetCount();

		// Initialize Database Service
		$database = new \Database\Service();

		// Dereference Working Class
		$workingClass = new $this->_modelName;

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
			if (class_exists($parameters['class_name'])) {
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

		// apply the order and sort direction
		if (!empty($controls['sort']) && !empty($controls['order'])) {
			$order_by_clause = " ORDER BY ";
			$sort_direction_clause = " `" . $controls['sort'] . "` " . strtoupper($controls['order']);
			$find_objects_query .= $order_by_clause . $sort_direction_clause;
		}
		elseif (!empty($controls['sort'])) {
			$order_by_clause = " ORDER BY ";
			$sort_direction_clause = " `" . $controls['sort'] . "` DESC";
			$find_objects_query .= $order_by_clause . $sort_direction_clause;
		}
		else {
			$find_objects_query .= " ORDER BY `".$this->_tableDefaultSortBy."` DESC";
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

	public function countMatching($parameters): int {
		$this->clearError();

		$database = new \Database\Service();
		$workingClass = new $this->_modelName;

		$count_query = "
			SELECT	COUNT(*)
			FROM	`".$workingClass->_tableName()."`
			WHERE	`".$workingClass->_tableIdColumn()."` = `".$workingClass->_tableIdColumn()."`
		";

		if (!empty($parameters['customer_id']) && is_numeric($parameters['customer_id'])) {
			$customer = new \Register\Customer($parameters['customer_id']);
			if ($customer->exists()) {
				$count_query .= " AND user_id = ?";
				$database->AddParam($parameters['customer_id']);
			}
			else {
				$this->error("Customer not found");
				return 0;
			}
		}

		if (!empty($parameters['class_name'])) {
			if (class_exists($parameters['class_name'])) {
				$count_query .= " AND class_name = ?";
				$database->AddParam($parameters['class_name']);
			}
			else {
				$this->error("Invalid class name");
				return 0;
			}
		}

		if (!empty($parameters['instance_id']) && is_numeric($parameters['instance_id'])) {
			$count_query .= " AND instance_id = ?";
			$database->AddParam($parameters['instance_id']);
		}

		if (!empty($parameters['change_type'])) {
			$count_query .= " AND class_method = ?";
			$database->AddParam($parameters,'change_type');
		}

		if (!empty($parameters['description'])) {
			if ($workingClass->validSearchString($parameters['description'])) {
				$count_query .= " AND description LIKE ?";
				$database->AddParam('%' . $parameters['description'] . '%');
			}
			else {
				$this->error("Invalid description");
				return 0;
			}
		}

		$rs = $database->Execute($count_query);
		if (!$rs) {
			$this->SQLError($database->ErrorMsg());
			return 0;
		}

		list($total) = $rs->FetchRow();
		return (int)$total;
	}

	public function batchDeleteAuditEvents($class_name, int $count) {
		$this->clearError();
		$this->resetCount();

		// Initialize Database Service
		$database = new \Database\Service();

		// Prepare Stored Procedure
		$database->trace(9);
		$database->debug = 'log';
		//$database->PrepareSP("batchDeleteAuditEvents");

		$database->AddParam($class_name);
		$database->AddParam($count);

		// Execute Query
		$result = $database->Execute('CALL batchDeleteAuditEvents(?, ?)');
		if (!$result) {
			$this->SQLError($database->ErrorMsg());
			return false;
		}

		return true;
	}
}
