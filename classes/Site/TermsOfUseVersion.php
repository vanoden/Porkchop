<?php
	namespace Site;

	class TermsOfUseVersion Extends \BaseModel {
	
		public $status;
		public $content;
		public $number;
		public $version_number;
		public $tou_id;

	
		/********************************************/
		/* Instance Constructor						*/
		/********************************************/
		public function __construct($id = 0) {

			// Set Table Name
			$this->_tableName = 'site_terms_of_use_versions';
			$this->_addFields(array('id','version_number', 'tou_id', 'status','content'));

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

			// get next version for the terms of use
			$params['version_number'] = $this->get_next_version($params['tou_id']);

			// Prepare Query
			$add_object_query = "
				INSERT
				INTO	`".$this->_tableName."`
				(		tou_id,version_number,status)
				VALUES	(?,?,?)
			";

			// Add Parameters
			$database->AddParam($tou->id);
			$database->AddParam($params['version_number']);
			$database->AddParam($params['status']);

			// Execute Query
			$rs = $database->Execute($add_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			// Fetch New ID
			$this->id = $database->Insert_ID();

			$this->addEvent('CREATION');
			if ($params['status'] == 'PUBLISHED') $this->addEvent('ACTIVATION');

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
			$event->add(array('version_id' => $this->id, 'type' => 'ACTIVATION'));
			if ($event->error()) {
				$this->error($event->error());
				return false;
			}

			// Bust Cache for Updated Object
			app_log("-----DELETING CACHE-------");
			$cache = new \Cache\Item($GLOBALS['_CACHE_'], 'latest_tou['.$this->tou_id.']');
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
			$event->add(array('version_id' => $this->id, 'type' => 'RETRACTION'));

			// Bust Cache for Updated Object
			app_log("-----DELETING CACHE-------");
			$cache = new \Cache\Item($GLOBALS['_CACHE_'], 'latest_tou['.$this->tou_id.']');
			$cache->delete();

			// Load Updated Details from Database
			return $this->details();
		}

		public function termsOfUse() {
			return new \Site\TermsOfUse($this->tou_id);
		}

		public function addEvent($type) {
			app_log("Do we have cache for tou ".$this->tou_id."?");
			$cache = new \Cache\Item($GLOBALS['_CACHE_'], "latest_tou[".$this->tou_id."]");
			if ($cache->exists()) {
				app_log("TOU Cache Must Be Destroyed");
				if (!$cache->delete()) {
					$this->error("Couldn't delete cache: ".$cache->error());
					return false;
				}
			}
			$event = new TermsOfUseEvent();
			if ($event->add(array('version_id' => $this->id, 'type' => $type))) return true;
			$this->error("Unable to add event: ".$event->error());
			return false;
		}

		public function addAction($user_id,$type) {
			$action = new TermsOfUseAction();
			if ($action->add(array('version_id' => $this->id, 'user_id' => $user_id, 'type' => $type))) return true;
			$this->error("Unable to add action: ".$action->error());
			return false;
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

		public function date_created() {
			$eventList = new TermsOfUseEventList();
			$event = $eventList->first(array('version_id' => $this->id, 'type' => 'CREATION'));
			if ($eventList->error()) {
				$this->error($eventList->error());
				return null;
			}
			elseif (!$event) {
				$event = new \Site\TermsOfUseEvent();
			}
			return $event->date_event;
		}

		public function date_published() {
			$eventList = new TermsOfUseEventList();
			$event = $eventList->last(array('version_id' => $this->id, 'type' => 'ACTIVATION'));
			if ($eventList->error()) {
				$this->error($eventList->error());
				return null;
			}
			elseif (!$event) {
				$event = new \Site\TermsOfUseEvent();
			}
			return $event->date_event;
		}

		public function date_retracted() {
			$eventList = new TermsOfUseEventList();
			$event = $eventList->last(array('version_id' => $this->id, 'type' => 'RETRACTION'));
			if ($eventList->error()) {
				$this->error($eventList->error());
				return null;
			}
			elseif (!$event) {
				$event = new \Site\TermsOfUseEvent();
			}
			return $event->date_event;
		}

		public function validContent($string) {
			if (preg_match('/\<script/',urldecode($string))) return false;
			else return true;
		}

		public function getByTermsOfUseIdVersionNumber($tou_id,$version_number) {
			$rs = $this->execute("SELECT id FROM `site_terms_of_use_versions` WHERE tou_id = ? AND version_number = ?", array($tou_id,$version_number));
			list($id) = $rs->FetchRow();
			if ($id) return $this->get($id);
			return false;
		}

		/**
		 * get the next highest version number
		 *
         * @param number $tou_id
		 */
		private function get_next_version($tou_id) {
			$rs = $this->execute("SELECT max(`version_number`) FROM `site_terms_of_use_versions` WHERE tou_id = ?", array($tou_id));
			list($number) = $rs->FetchRow();
			if (is_numeric($number)) return $number + 1;
			return 1;
		}
	}
