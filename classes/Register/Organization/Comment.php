<?php
	namespace Register\Organization;

	class Comment extends \BaseModel {
		public $user_id;
		public $content;
		public $timestamp;

		public function __construct($id = 0) {
			$this->_tableName = 'register_organization_comments';
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add ($parameters = array()) {

			if (! $GLOBALS['_SESSION_']->customer->can("manage organization comments")) {
				$this->error("Not enough privileges");
				return false;
			}
			if (! $parameters['organization_id']) {
				$this->error("organization not specified");
				return false;
			}
			$organization = new \Register\Organization($parameters['organization_id']);
			if (! $organization->id) {
				$this->error("organization not found");
				return false;
			}

			$add_object_query = "
				INSERT
				INTO	register_organization_comments
				(
					organization_id,
					user_id,
					timestamp,
					content
				)
				VALUES
				(
					?,?,sysdate(),?
				)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array($organization->id,$GLOBALS['_SESSION_']->customer->id,$parameters["content"])
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();
			
            // audit the add event
            $auditLog = new \Site\AuditLog\Event();
            $auditLog->add(array(
                'instance_id' => $this->id,
                'description' => 'Added new '.$this->_objectName(),
                'class_name' => get_class($this),
                'class_method' => 'add'
            ));

			return $this->details();
		}

		public function details (): bool {

			if (! $this->id) {
				$this->_error = "id required";
				return false;
			}

			$get_object_query = "
				SELECT	id,
						user_id,
						organization_id,
						timestamp,
						content
				FROM	register_organization_comments
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);

			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$object = $rs->FetchNextObject(false);
			$this->id = $object->id;
			$this->user_id = $object->user_id;
			$this->timestamp = $object->timestamp;
			$this->content = $object->content;

			return true;
		}

		public function user(): ?\Register\Customer {
			if ($this->user_id) {
				$user = new \Register\Customer($this->user_id);
				return $user;
			}
			return null;
		}
	}
