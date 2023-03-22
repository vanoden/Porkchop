<?php
	namespace Site;

	class TermsOfUseActionList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Site\TermsOfUseAction';
		}

		public function find($params = [],$controls = []): array {
			if (isset($params['tou_id'])) {
				$tou = new \Site\TermsOfUse($params['tou_id']);
				if (empty($tou->id)) {
					$this->error("Terms Of Use Record Not Found");
					return array();
				}
			}

			if (isset($params['status'])) {
				$tou = new \Site\TermsOfUse();
				if (!$tou->validStatus($params['status'])) {
					$this->error("Invalid Status '".$params['status']."'");
					return array();
				}
			}

			return parent::find($params,$controls);
		}
	}