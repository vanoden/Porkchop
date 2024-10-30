<?php
	namespace Site;

	class TermsOfUseVersionList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Site\TermsOfUseVersion';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			if (isset($parameters['tou_id'])) {
				$tou = new \Site\TermsOfUse($parameters['tou_id']);
				if (empty($tou->id)) {
					$this->error("Terms Of Use Record Not Found");
					return array();
				}
			}

			if (isset($parameters['status'])) {
				$tou = new \Site\TermsOfUse();
				if (!$tou->validStatus($parameters['status'])) {
					$this->error("Invalid Status '".$parameters['status']."'");
					return array();
				}
			}
			$objects = parent::findAdvanced($parameters,$advanced,$controls);
			if (parent::error()) {
				$this->error(parent::error());
			}
			return $objects;
		}

		public function latestPublished($tou_id): TermsOfUseVersion {
			$this->clearError();

			$tou = new TermsOfUseVersion($tou_id);
			if (empty($tou->id)) {
				$this->error("Terms of Use Record Not Found");
				return null;
			}
			$eventList = new TermsOfUseEventList();
			list($last) = $eventList->find(array('tou_id' => $tou->id, 'type' => 'ACTIVATION'), array('sort' => 'date_event','order' => 'desc','limit' => 1));
			if (!isset($last)) $last = new TermsOfUseVersion;
			return $last;
		}
	}
