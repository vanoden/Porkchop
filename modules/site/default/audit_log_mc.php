<?php
	$site = new \Site();
	$page = $site->page();;
	$page->requirePrivilege('see audit logging');

	$parameters = array();
	$parameters['status'] = array();

	$auditClass = new \Site\AuditLog();
	$classList = $auditClass->classes();

	// extract sort and order parameters from request
	$sort_direction = isset($_REQUEST['sort_by']) ? $_REQUEST['sort_by'] : '';
	$order_by = isset($_REQUEST['order_by']) ? $_REQUEST['order_by'] : 'desc';
	$parameters['order_by'] = $order_by;
	$parameters['sort_direction']= $sort_direction;

	// get audits based on current search
	if (!empty($_REQUEST['btn_submit'])) {
		if (empty($_REQUEST['class_name'])) {
			$page->addError("Please select a class to view audit logs.");
		}
		elseif (!class_exists($_REQUEST['class_name'])) {
			$page->addError("Class does not exist.");
		}
		else {
			$parameters['class_name'] = $_REQUEST['class_name'];
			$class = new $_REQUEST['class_name'];
			if (empty($_REQUEST['code'])) {
				$page->addError("Please enter an instance code");
			}
			elseif (!$class->validCode($_REQUEST['code'])) {
				$page->addError("Invalid instance code.");
			}
			elseif (!$class->get($_REQUEST['code'])) {
				$page->addError("Instance does not exist.");
			}
			else {
				$parameters['instance_id'] = $class->id;

				if (!isset($_REQUEST['pagination_start_id'])) $_REQUEST['pagination_start_id'] = 0;

				// find audits
				$auditList = new \Site\AuditLog\EventList();
				$audits = $auditList->find($parameters);

				// paginate results
				$pageNumber = isset($_GET['pagination_start_id']) && is_numeric($_GET['pagination_start_id']) ? (int)$_GET['pagination_start_id'] : 1;
				$recordsPerPage = 10;
				$offset = ($pageNumber - 1) * $recordsPerPage;
				$totalResults = count($audits);
				$auditsCurrentPage = array_slice($audits, $offset, $recordsPerPage);
				$totalPages = ceil($totalResults / $recordsPerPage);

				if (!isset($_REQUEST['start'])) $_REQUEST['start'] = 0;
				if ($_REQUEST['start'] < $recordsPerPage)
					$prev_offset = 0;
				else
					$prev_offset = $_REQUEST['start'] - $recordsPerPage;
					
				$next_offset = $_REQUEST['start'] + $recordsPerPage;
				$last_offset = $totalResults - $recordsPerPage;

				if ($next_offset > $totalResults) $next_offset = $_REQUEST['pagination_start_id'] + $totalResults;

				$pagination = new \Site\Page\Pagination();
				$pagination->forwardParameters(array('add','update','delete','btn_submit','sort_by','order_by'));
				$pagination->size($recordsPerPage);
				$pagination->count($totalResults);
				$display_results = true;
			}
		}
	}