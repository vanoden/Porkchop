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
				return [];
			}

			$classes = [];
			while (list($class_name) = $rs->FetchRow()) {
				$classes[] = $class_name;
			}
			return $classes;
		}

		public function countEvents(int $instanceId, ?string $className = null): int {
			$this->clearError();
			$_database = new \Database\Service();

			$count_query = "
				SELECT	COUNT(*)
				FROM	site_audit_events
				WHERE	instance_id = ?
			";
			$_database->AddParam($instanceId);
			if (!empty($className)) {
				$count_query .= "
					AND	class_name = ?
				";
				$_database->AddParam($className);
			}

			$rs = $_database->Execute($count_query);
			if (! $rs) {
				$this->SQLError("Counting classes: " . $_database->ErrorMsg());
				return 0;
			}

			list($count) = $rs->FetchRow();
			return intval($count);
		}
	}