<?
	require_module('action');
	require_module('monitor');

	if ($_REQUEST['btn_submit']) {
		$request = new ActionRequest();
		$request->add(
			array(
				'status'			=> 'NEW',
				'user_requested'	=> $_REQUEST['customer_id'],
				'user_assigned'		=> $_REQUEST['user_assigned'],
				'description'		=> $_REQUEST['description']
			)
		);
		if ($request->error) {
			$GLOBALS['_page']->error = "Error recording request: ".$request->error;
			$form_complete = 0;
		}
		else {
			$task = new ActionTask();
			$task->add(
				array(
					'request_id'		=> $_REQUEST['request_id'],
					'date_request'		=> date('Y-m-d H:i:s'),
					'user_requested'	=> $_REQUEST['customer_id'],
					'user_assigned'		=> $_REQUEST['user_assigned'],
					'type_id'			=> $_REQUEST['type_id'],
					'status'			=> 'NEW',
					'asset_id'			=> $_REQUEST['asset_id'],
					'request_id'		=> $request->id,
					'description'		=> $_REQUEST['description']
				)
			);
			if ($task->error) {
				$GLOBALS['_page']->error = "Error recording task: ".$task->error;
				$form_complete = 0;
			}
			else
				$form_complete = 1;
		}
	}
	else {
		$form_complete = 0;
	}
	
	$_types = new ActionTaskTypes();
	$types = $_types->find();

	$_customer = new RegisterCustomer();
	$customers = $_customer->find(
		array(
			'_sort'	=> 'first_name'
		)
	);
	
	$_tech = new RegisterCustomer();
	$techs = $_tech->find(
		array(
			'_sort'	=> 'first_name'
		)
	);
	
	$asset = new MonitorAsset();
	$asset->get($_REQUEST['asset_code']);
?>
