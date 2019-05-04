<?php
	namespace Solvay;

	class Collection extends \Monitor\Collection {
		public function addCylinder($cylinder_code,$weight) {
			$cylinder = new \Monitor\Cylinder();
			if (! $cylinder->get($cylinder_code)) {
				$cylinder->add(array('code'=>$cylinder_code));
			}
			$add_cylinder_query = "
				INSERT
				INTO	monitor_collection_cylinders
				VALUES (?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_cylinder_query,
				array($cylinder->id,$weight)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Monitor::Collection::addCylinder: ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return true;
		}
	}
?>
