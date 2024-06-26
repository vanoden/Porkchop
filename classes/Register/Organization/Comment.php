<?php
	namespace Register\Organization;

	class Comment {
		public $error;
		public $id;
		public $user;
		public $content;
		public $timestamp;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add ($parameters = array()) {

			if (! $GLOBALS['_SESSION_']->customer->can("manage organization comments")) {
				$this->error = "Not enough privileges";
				return null;
			}
			if (! $parameters['organization_id']) {
				$this->error = "organization not specified";
				return null;
			}
			$organization = new \Register\Organization($parameters['organization_id']);
			if (! $organization->id) {
				$this->error = "organization not found";
				return null;
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
				array($organization->id,$GLOBALS['_SESSION_']->customer->id,$content)
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Register::Organization::Comment::add(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
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

		public function update ($parameters = array()) {

		}

		public function details () {

			if (! $this->id) {
				$this->_error = "id required";
				return null;
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
				$this->error = "SQL Error in Register::Organization::Comment::details(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$object = $rs->FetchNextObject(false);
			$this->id = $object->id;
			$this->user = new \Register\User($this->user_id);
			$this->timestamp = $object->timestamp;
			$this->content = $object->content;

			return 1;
		}
	}
