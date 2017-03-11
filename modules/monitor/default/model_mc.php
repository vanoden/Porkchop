<?
	if ($_REQUEST['id']) {
		$model = new \Monitor\Model($_REQUEST['id']);
	}
	else {
		app_log("Loading model '".$GLOBALS['_REQUEST_']->query_vars_array[0]."'");
		$model = new \Monitor\Model();
		$model->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}
	
	if ($_REQUEST['btn_submit']) {
		$parameters = array();
		$parameters['code'] = $_REQUEST['code'];
		$parameters['name'] = $_REQUEST['name'];
		$parameters['units'] = $_REQUEST['units'];
		if (preg_match('/^\d+$/',$_REQUEST['calibration_multiplier'])) {
			$parameters['calibration_multiplier'] = $_REQUEST['calibration_multiplier'];
		}
		if (preg_match('/^\d+$/',$_REQUEST['calibration_offset'])) {
			$parameters['calibration_offset'] = $_REQUEST['calibration_offset'];
		}
		$parameters['calculation_type'] = $_REQUEST['calculation_type'];
		
		if ($model->id) {
			$model->update($parameters);
			if ($model->error) {
				$this->error = "Failed to update model: ".$model->error;
			}
			else {
				$this->success  = "Model updated";
			}
		}
		else {
			$model->add($parameters);
			if ($model->error) {
				$this->error = "Failed to add model: ".$model->error;
			}
			else {
				$this->success = "Model created";
			}
		}
	}
?>