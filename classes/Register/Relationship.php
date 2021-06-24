<?php
	namespace Register;

    class Relationship {
		public $error;
		public $parent_id;
		public $person_id;

		public function add($parent_id,$person_id)
		{
			$add_relationship_query = "
				INSERT
				INTO	register_relations
				(		parent_id,
						person_id
				)
				VALUES
				(		?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_relationship_query,
				array($parent_id,
					  $person_id
				)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in RegisterRelationship::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this;
		}
		public function delete($parent_id,$person_id)
		{
			$delete_relationship_query = "
				DELETE
				FROM	register_relations
				WHERE	parent_id = ?
				AND		person_id = ?
			";
			$GLOBALS['_database']->Execute(
				$delete_relationship_query,
				array($parent_id,
					  $person_id
				)
			);
			if (! $GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in RegisterRelationship::delete: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}
		public function exists($parent_id,$person_id)
		{
			$check_relationship_query = "
				SELECT	1
				FROM	register_relations
				WHERE	parent_id = ?
				AND		person_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$check_relationship_query,
				array($parent_id,
					  $person_id
				)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in RegisterRelationship::exists: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($exists) = $rs->FetchRow();
			return $exists;
		}
		public function parents($person_id)
		{
			$get_parents_query = "
				SELECT	parent_id
				FROM	register_relations
				WHERE	person_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_parents_query,
				array($person_id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in RegisterRelationship::parents: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$parents = array();
			while (list($parent_id) = $rs->FetchRow())
			{
				$_person = new RegisterPerson();
				$parent = $_person->details($parent_id);
				array_push($parents,$parent);
			}
			return $parents;
		}
		public function children($parent_id)
		{
			$get_child_query = "
				SELECT	person_id
				FROM	register_relations
				WHERE	parent_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_child_query,
				array($parent_id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in RegisterRelationship::children: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$children = array();
			while (list($person_id) = $rs->FetchRow())
			{
				$_person = new RegisterPerson();
				$person = $_person->details($person_id);
				array_push($children,$person);
			}
			return $children;
		}
	}
