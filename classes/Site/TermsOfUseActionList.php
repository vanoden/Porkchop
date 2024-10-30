<?php
namespace Site;

class TermsOfUseActionList extends \BaseListClass {
	public function __construct() {
		$this->_modelName = '\Site\TermsOfUseAction';
	}

	public function findAdvanced($parameters, $advanced, $controls): array {
		$this->clearError();
		$this->resetCount();
		
		if (isset($params['tou_id']) && is_numeric($params['tou_id'])) {
			$tou = new \Site\TermsOfUse($params['tou_id']);
			if ($tou->error()) {
				$this->error($tou->error());
				return array();
			}
			if (empty($tou->id)) {
				$this->error("Terms Of Use Record Not Found");
				return array();
			}
		}

		if (isset($params['version_id'])) {
			$version = new \Site\TermsOfUseVersion($params['version_id']);
			if ($version->error()) {
				$this->error($version->error());
				return array();
			}
			if (empty($version->id)) {
				$this->error("Version not found");
				return array();
			}
		}

		if (isset($params['type'])) {
			$action = new $this->_modelName;
			if (!$action->validType($params['type'])) {
				$this->error("Invalid type '" . $params['type'] . "'");
				return array();
			}
		}

		return parent::findAdvanced($parameters, $advanced, $controls);
	}
}
