<?php
	namespace Site;

	class TermsOfUseAction Extends \BaseModel {

		public $version_id;
		public $user_id;
		public $type;
		public $date_action;

		/********************************************/
		/* Instance Constructor						*/
		/********************************************/
		public function __construct(int $id = null) {
			// Set Table Name
			$this->_tableName = 'site_terms_of_use_actions';

			// Set cache key name - MUST Be Unique to Class
			$this->_cacheKeyPrefix = $this->_tableName;

			// Possible Types
			$this->_addTypes(array('VIEWED','ACCEPTED','DECLINED'));

			// Load Record for Specified ID if given
			if (isset($id) && is_numeric($id)) {
				$this->id = $id;
				$this->details();
			}
		}

		/********************************************/
		/* Add New Object Record Given Parameters	*/
		/* Must include non-nullable fields.		*/
		/* Others should be handled in update().	*/
		/********************************************/
		public function add($params = []): bool {
			
			// Clear Any Existing Errors
			$this->clearError();

			// Initialize Services
			$porkchop = new \Porkchop();
			$database = new \Database\Service();

			// Default New Object Code If Not Provided
			if (empty($params['code'])) $params['code'] = $porkchop->uuid();
			if (empty($params['status'])) $params['status'] = 'NEW';
			if (empty($params['version_id'])) {
				$this->error("version_id required");
				return false;
			}
			else {
				$version = new \Site\TermsOfUseVersion($params['version_id']);
				if ($version->error()) {
					$this->error($version->error());
					return false;
				}
				elseif (!$version->id) {
					$this->error("Version Not Found");
					return false;
				}
			}

			// Prepare Query
			$add_object_query = "
				INSERT
				INTO	`".$this->_tableName."`
				(		version_id,user_id,type,date_action)
				VALUES
				(		?,?,?,sysdate())
			";

			// Add Parameters
			$database->AddParam($version->id);
			$database->AddParam($GLOBALS['_SESSION_']->customer->id);
			$database->AddParam($params['type']);

			// Execute Query
			$rs = $database->Execute($add_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Fetch New ID
			$this->id = $database->Insert_ID();
			
            // audit the add event
            $auditLog = new \Site\AuditLog\Event();
            $auditLog->add(array(
                'instance_id' => $this->id,
                'description' => 'Added new '.$this->_objectName(),
                'class_name' => get_class($this),
                'class_method' => 'add'
            ));

			// No Update, Load Details
			return $this->details();
		}
	}
