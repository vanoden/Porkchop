<?php
	namespace Site;

	class AuditLog extends \BaseClass{
		public function classes() {
			$this->clearError();
			$_database = new \Database\Service();

			$get_classes_query = "
				SELECT	class_name
				FROM	site_audit_events
				GROUP BY class_name
				ORDER BY class_name
			";
			$rs = $_database->Execute($get_classes_query);
			if (! $rs) {
				$this->SQLError("Getting classes");
				return false;
			}

			$classes = [];
			while (list($class_name) = $rs->FetchRow()) {
				$classes[] = $class_name;
			}
			return $classes;
		}
	}