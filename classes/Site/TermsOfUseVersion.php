<?php
	namespace Site;

	class TermsOfUseVersion Extends \BaseModel {
		public $status;
		public $content;
		public $number;

		/********************************************/
		/* Instance Constructor						*/
		/********************************************/
		public function __construct($id = 0) {
			// Set Table Name
			$this->_tableName = 'site_terms_of_use_versions';
			$this->_addFields(array('status','content'));

			// Set cache key name - MUST Be Unique to Class
			$this->_cacheKeyPrefix = $this->_tableName;

			// Add Status(es) for Validation
			$this->_addStatus(array('NEW','PUBLISHED','RETRACTED'));

			// Load Record for Specified ID if given
			parent::__construct($id);
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

			$tou = new \Site\TermsOfUse($params['tou_id']);
			if (!$tou->id) {
				$this->error("Terms of Use item not found");
				return false;
			}
			if (!isset($params['status'])) $params['status'] = 'NEW';

			if (!$this->validStatus($params['status'])) {
				$this->error("Invalid status");
				return false;
			}

			$versionList = new \Site\TermsOfUseVersionList();

			// Prepare Query
			$add_object_query = "
				INSERT
				INTO	`".$this->_tableName."`
				(		tou_id,status)
				VALUES	(?,?)
			";

			// Add Parameters
			$database->AddParam($tou->id);
			$database->AddParam($params['status']);

			// Execute Query
			$rs = $database->Execute($add_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			// Fetch New ID
			$this->id = $database->Insert_ID();

			$event = new TermsOfUseEvent();
			$event->add(array('version_id' => $this->id, 'status' => 'NEW'));
			if ($params['status'] == 'PUBLISHED') $event->add(array('version_id' => $this->id, 'status' => 'PUBLISHED'));

			// Update Any Nullable Values
			return $this->update($params);
		}

		/********************************************/
		/* Update Existing Record					*/
		/********************************************/
		public function publish(): bool {
			// Clear Any Existing Errors
			$this->clearError();

			// Load Existing Data for comparison
			$this->details();

			// Initialize Services
			$database = new \Database\Service();
			$cache = $this->cache();

			if ($this->status == "PUBLISHED") return true;
			if ($this->status == "RECTRACTED") {
				$this->error("Cannot publish a retracted Terms Of Use Version");
				return false;
			}

			// Prepare Query
			$update_object_query = "
				UPDATE	`".$this->_tableName."`
				SET		status = 'PUBLISHED'
				WHERE	id = ?";
			$database->AddParam($this->id);

			// Execute Query
			$database->Execute($update_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$event = new TermsOfUseEvent();
			$event->add(array('version_id' => $this->id, 'status' => 'PUBLISHED'));

			// Bust Cache for Updated Object
			$cache->delete();

			// Load Updated Details from Database
			return $this->details();
		}

		/********************************************/
		/* Update Existing Record					*/
		/********************************************/
		public function retract(): bool {
			// Clear Any Existing Errors
			$this->clearError();

			// Load Existing Data for comparison
			$this->details();

			// Initialize Services
			$database = new \Database\Service();
			$cache = $this->cache();

			if ($this->status == "RETRACTED") return true;

			// Prepare Query
			$update_object_query = "
				UPDATE	`".$this->_tableName."`
				SET		status = 'RETRACTED'
				WHERE	id = ?";
			$database->AddParam($this->id);

			// Execute Query
			$database->Execute($update_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$event = new TermsOfUseEvent();
			$event->add(array('version_id' => $this->id, 'status' => 'RETRACTED'));

			// Bust Cache for Updated Object
			$cache->delete();

			// Load Updated Details from Database
			return $this->details();
		}

		public function events(): array {
			$eventList = new TermsOfUseEventList();
			return $eventList->find(array('tou_id' => $this->id));
		}

		public function actions(): array {
			$eventList = new TermsOfUseEventList();
			return $eventList->find(array('tou_id' => $this->id));
		}

		public function number() {
			return $this->date_event();
		}

		public function validContent($string) {
			if (preg_match('/\<script/',urldecode($string))) return false;
			else return true;
		}
	}