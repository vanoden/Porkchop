<?php
namespace Build;

class Commit extends \BaseModel {
	
	public $repository_id;
	public $hash;
	public $timestamp;
	public $author_id;

	public function __construct($id = null) {
		$this->_tableName = 'build_commits';
		if (isset($id) && is_numeric($id)) {
			$this->id = $id;
			$this->details();
		}
	}

	public function add($parameters = []) {
		if ($parameters['repository_id']) {
			$repository = new Repository($parameters['repository_id']);
			if (!$repository->id) {
				$this->_error = "Repository not found";
				return false;
			}
		} else {
			$this->_error = "Repository id required";
		}
		if (!isset($parameters['hash']) || !preg_match('/^\w+$/', $parameters['hash'])) {
			$this->_error = "hash required";
			return false;
		}

		$add_object_query = "
				INSERT
				INTO	build_commits
				(		repository_id,hash,`timestamp`)
				VALUES
				(		?,?,sysdate())
			";
		$GLOBALS['_database']->Execute($add_object_query, array($repository->id, $parameters['number']));
		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return false;
		}
		$this->id = $GLOBALS['_database']->Insert_ID();

		// audit the add event
		$auditLog = new \Site\AuditLog\Event();
		$auditLog->add(array(
			'instance_id' => $this->id,
			'description' => 'Added new ' . $this->_objectName(),
			'class_name' => get_class($this),
			'class_method' => 'add'
		));

		return $this->update($parameters);
	}

	public function _objectName() {
		if (!isset($caller)) {
			$trace = debug_backtrace();
			$caller = $trace[2];
		}

		$class = isset($caller['class']) ? $caller['class'] : null;
		if (preg_match('/(\w[\w\_]*)$/',$class,$matches)) $classname = $matches[1];
		else $classname = "Object";
		return $classname;
	}	

	public function update($parameters = array()): bool {
		$update_object_query = "
				UPDATE	build_commits
				SET		id = id";

		$bind_params = array();
		if ($parameters['author']) {
			$author = new \Register\Customer();
			if (!$author->get($parameters['author'])) {
				$this->_error = "Author not found";
				return false;
			}
			$update_object_query .= ",
						author_id = ?";
			array_push($bind_params, $author->id);
		} elseif ($parameters['author_id']) {
			$author = new \Register\Customer($parameters['author_id']);
			if (!$author->id) {
				$this->_error = "Author not found";
				return false;
			}
			$update_object_query .= ",
						author_id = ?";
			array_push($bind_params, $author->id);
		}
		$update_object_query .= "
				WHERE	id = ?";
		array_push($bind_params, $this->id);

		$GLOBALS['_database']->Execute($update_object_query, $bind_params);
		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return false;
		}

		// audit the update event
		$auditLog = new \Site\AuditLog\Event();
		$auditLog->add(array(
			'instance_id' => $this->id,
			'description' => 'Updated '.$this->_objectName(),
			'class_name' => get_class($this),
			'class_method' => 'update'
		));		
		
		return $this->details();
	}

	public function get($repo_id, $hash) {
		$repository = new \Storage\Repository($repo_id);
		if (!$repository->id) {
			$this->_error = "Repository not found";
			return false;
		}

		$get_object_query = "
				SELECT	id
				FROM	build_commits
				WHERE	repository_id = ?
				AND		hash = ?
			";

		$rs = $GLOBALS['_database']->Execute($get_object_query, array($repository->id, $hash));
		if (!$rs) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return false;
		}
		list($this->id) = $rs->FetchRow();
		if ($this->id > 0) {
			app_log("Found commit " . $this->id);
			return $this->details();
		}
		return false;
	}
	public function details(): bool {
		$get_object_query = "
				SELECT	*
				FROM	build_commits
				WHERE	id = ?
			";
		$rs = $GLOBALS['_database']->Execute($get_object_query, array($this->id));
		if (!$rs) {
			$this->_error = "SQL Error in Build::Commit::details(): " . $GLOBALS['_database']->ErrorMsg();
			return false;
		}
		$object = $rs->FetchNextObject(false);
		if ($object->id) {
			$this->id = $object->id;
			$this->repository_id = $object->repository_id;
			$this->hash = $object->hash;
			$this->timestamp = $object->timestamp;
			$this->author_id = $object->author_id;
			return true;
		} else {
			$this->id = null;
			return false;
		}
	}
}
