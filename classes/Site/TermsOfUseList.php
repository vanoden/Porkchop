<?php
	namespace Site;

	class TermsOfUseList Extends \BaseListClass {
		public function find(array $params = array()): array {
			$this->clearError();
			$this->resetCount();

			$database = new \Database\Service();

			$get_objects_query = "
				SELECT	id
				FROM	site_terms_of_use
				WHERE	id = id";

			if (!empty($params['name'])) {
				$get_objects_query .= "
				AND		name = ?";
				$database->AddParam($params['name']);
			}
			$rs = $database->Execute($get_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return array();
			}

			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$tou = new TermsOfUse($id);
				array_push($objects,$tou);
				$this->incrementCount();
			}
			return $objects;
		}
	}