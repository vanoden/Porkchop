<?php
	$site = new \Site();
	$page = $site->page();;
	$page->requirePrivilege('see audit logging');

	$parameters = array();
	$parameters['status'] = array();
	$can_proceed = true;

	$auditClass = new \Site\AuditLog();
	$classList = $auditClass->classes();

	if (count($GLOBALS['_REQUEST_']->query_vars_array) > 0 && !empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$parameters['class_name'] = preg_replace('/\:\:/','\\',$GLOBALS['_REQUEST_']->query_vars_array[0]);
		$_REQUEST['class_name'] = $parameters['class_name'];
		if (count($GLOBALS['_REQUEST_']->query_vars_array) > 1) {
			$parameters['code'] = $GLOBALS['_REQUEST_']->query_vars_array[1];
			$_REQUEST['code'] = $parameters['code'];
			$_REQUEST['btn_submit'] = 'Apply Filter';
		}
	}

	// extract sort and order parameters from request
	$sort_direction = $_REQUEST['sort_by'] ?? '';
	$order_by = $_REQUEST['order_by'] ?? 'desc';

	// get audits based on current search
	$btn_submit = $_REQUEST['btn_submit'] ?? null;
	if (!empty($_REQUEST['btn_submit'])) {
		$class_name = $_REQUEST['class_name'] ?? null;
		if (empty($class_name)) {
			$page->addError("Please select a class to view audit logs.");
		}
		elseif (!class_exists($class_name)) {
			$page->addError("Class does not exist.");
		}
		else {
			$parameters['class_name'] = $class_name;
			$class = new $class_name();
			$code = $_REQUEST['code'] ?? $parameters['code'] ?? null;
			$parameters['code'] = $code;
			if (empty($code)) {
				$page->addError("Please enter an instance code");
			}
			elseif (!$class->validCode($code)) {
				$page->addError("Invalid instance code.");
			}
			elseif (!$class->get($code)) {
				if ($class->error()) {
					$page->addError("Error retrieving instance: " . $class->error());
				}
				else {
					$page->addError("Instance not found.");
				}
			}
			else {
				$parameters['instance_id'] = $class->id;

				$pagination_start_id = $_REQUEST['pagination_start_id'] ?? 0;
				if (!is_numeric($pagination_start_id)) $pagination_start_id = 0;
				$pagination_start_id = (int)$pagination_start_id;
				if ($pagination_start_id < 0) $pagination_start_id = 0;

				$recordsPerPage = 10;

				// find audits
				$controls = array(
					'sort' => $sort_direction,
					'order' => $order_by,
					'offset' => $pagination_start_id,
					'limit' => $recordsPerPage
				);
				$auditList = new \Site\AuditLog\EventList();
				$auditsCurrentPage = $auditList->find($parameters, $controls);
				if ($auditList->error()) {
					$page->addError("Error retrieving audits: " . $auditList->error());
					$auditsCurrentPage = [];
				}

				$totalResults = $auditList->countMatching($parameters);
				if ($auditList->error()) {
					$page->addError("Error counting audits: " . $auditList->error());
					$totalResults = 0;
				}

				$pageNumber = (int)floor($pagination_start_id / $recordsPerPage) + 1;

				$pagination = new \Site\Page\Pagination();
				$pagination->forwardParameters(array('class_name','code','btn_submit','sort_by','order_by'));
				$pagination->size($recordsPerPage);
				$pagination->count($totalResults);
				$display_results = true;
			}
		}
	}