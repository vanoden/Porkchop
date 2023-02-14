<?php
	namespace Site;

	class TermsOfUseVersionList Extends \BaseListClass {
		public function find(array $params = array()): array {
			$this->clearError();
			$this->resetCount();

			$database = new \Database\Service();

			$get_objects_query = "
				SELECT	id
				FROM	site_terms_of_use_versions
				WHERE	id = id";

			if (isset($params['tou_id'])) {
				$tou = new \Site\TermsOfUse($params['tou_id']);
				if ($tou->id) {
					$get_objects_query .= "
					AND		tou_id = ?";
					$database->AddParam($tou->id);
				}
				else {
					$this->error("Terms Of Use Record Not Found");
					return array();
				}
			}

			if (isset($params['status'])) {
				$tou = new \Site\TermsOfUse();
				if ($tou->validStatus($params['status'])) {
					$get_objects_query .= "
					AND		status = ?";
					$database->AddParam($params['status']);
				}
				else {
					$this->error("Invalid Status");
					return array();
				}
			}

			$rs = $database->Execute($get_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return array();
			}

			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$version = new \Site\TermsOfUseVersion($id);
				array_push($objects,$version);
				$this->incrementCount();
			}
			return $objects;
		}

		public function latestPublished(): TermsOfUseVersion {
			$this->clearError();

			$eventList = new TermsOfUseversionList();
			$last = $eventList->last(array('type' => 'PUBLISH'));
			if (!isset($last)) $last = new TermsOfUseVersion;
			return $last;
		}
	}