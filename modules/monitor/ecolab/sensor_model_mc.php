<?php
	$page = new \Site\Page();
	$page->requireRole("monitor admin");

	if (isset($_REQUEST['id'])) {
		$model = new \Monitor\Sensor\Model($_REQUEST['id']);
	} else {
		$model = new \Monitor\Sensor\Model();
		if (!$model->get($_REQUEST['code'])) $page->addError("Sensor Model Not FOund");
	}

	if (isset($_REQUEST['btn_submit'])) {
		$parameters = array(
			"name"						=> $_REQUEST['name'],
			"data_type"					=> $_REQUEST['data_type'],
			"units"						=> $_REQUEST['units'],
			"calibration_offset"		=> $_REQUEST['calibration_offset'],
			"calibration_mulitplier"	=> $_REQUEST['calibration_multiplier']
		);
		if (isset($_REQUEST['id'])) {
			$parameters['code'] = $_REQUEST['code'];
		}

		if (isset($model->id) && $model->id > 0) {
			if ($model->update($parameters)) {
				$page->success = "Update Complete";
			}
			else {
				$page->addError("Error updating model: ".$model->error());
			}
		}
		else {
			if ($model->add($parameters)) {
				$page->success = "Model Added";
			}
			else {
				$page->addError("Error adding model: ".$model->error());
			}
		}
	}
