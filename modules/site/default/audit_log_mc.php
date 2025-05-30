<?php
	$site = new \Site();
	$page = $site->page();;
	$page->requirePrivilege('see audit logging');

	$parameters = array();
	$parameters['status'] = array();
	$can_proceed = true;

	$request = new \HTTP\Request();
	$auditClass = new \Site\AuditLog();
	$classList = $auditClass->classes();

	// extract sort and order parameters from request
	$sort_direction = $_REQUEST['sort_by'] ?? '';
	$order_by = $_REQUEST['order_by'] ?? 'desc';
	$parameters['order_by'] = $order_by;
	$parameters['sort_direction']= $sort_direction;

	// get audits based on current search
	$btn_submit = $_REQUEST['btn_submit'] ?? null;
	if ($request->validText($btn_submit)) {
		$class_name = $_REQUEST['class_name'] ?? null;
		if (empty($class_name)) {
			$page->addError("Please select a class to view audit logs.");
			$can_proceed = false;
		}
		elseif (!class_exists($class_name)) {
			$page->addError("Class does not exist.");
			$can_proceed = false;
		}
		else {
			$parameters['class_name'] = $class_name;
			$class = new $class_name();
			$code = $_REQUEST['code'] ?? null;
			if (empty($code)) {
				$page->addError("Please enter an instance code");
				$can_proceed = false;
			}
			elseif (!$class->validCode($code)) {
				$page->addError("Invalid instance code.");
				$can_proceed = false;
			}
			elseif (!$class->get($code)) {
				$page->addError("Instance does not exist.");
				$can_proceed = false;
			}
			else {
				$parameters['instance_id'] = $class->id;

				$pagination_start_id = $_REQUEST['pagination_start_id'] ?? 0;
				if (!$request->validInteger($pagination_start_id)) $pagination_start_id = 0;

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

				$start = $_REQUEST['start'] ?? 0;
				if (!$request->validInteger($start)) $start = 0;
				
				if ($start < $recordsPerPage)
					$prev_offset = 0;
				else
					$prev_offset = $start - $recordsPerPage;
					
				$next_offset = $start + $recordsPerPage;
				$last_offset = $totalResults - $recordsPerPage;

				if ($next_offset > $totalResults) $next_offset = $pagination_start_id + $totalResults;

				$pagination = new \Site\Page\Pagination();
				$pagination->forwardParameters(array('add','update','delete','btn_submit','sort_by','order_by'));
				$pagination->size($recordsPerPage);
				$pagination->count($totalResults);
				$display_results = true;
			}
		}
	}