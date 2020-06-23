<?php
	namespace Engineering;

	class Task {
		private $_error;
		public $id;
		public $code;
		public $title;
		public $description;
		public $status;
		public $type;
		public $estimate;
		public $date_added;
		public $date_due;
		public $priority;
		public $prerequisite_id;
		private $release_id;
		private $product_id;
		private $requested_id;
		private $assigned_id;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			if (isset($parameters['code']) && strlen($parameters['code'])) {
				if (preg_match('/^[\w\-\.\_\s]+$/',$parameters['code'])) {
					$code = $parameters['code'];
				} else {
					$this->_error = "Invalid code";
					return false;
				}
			} else {
				$code = uniqid();
			}

			$check_dups = new Task();
			if ($check_dups->get($code)) {
				$this->_error = "Duplicate code '$code'";
				return false;
			}

			// process any prerequisite task that may have been passed
			$prerequisite_id = NULL;
			if (!empty($parameters['prerequisite_id'])) {
				$prerequisiteTask = new Task($parameters['prerequisite_id']);
				if (! $prerequisiteTask->id) {
					$this->_error = "Prerequisite task not found";
					return false;
				}
				$prerequisite_id = $parameters['prerequisite_id'];
			}

			if (isset($parameters['type'])) {
				if ($this->_valid_type($parameters['type'])) {
					$type = strtoupper($parameters['type']);
				}
				else {
					$this->_error = "Invalid Task Type";
					return false;
				}
			}
			else {
				$this->_error = "Type is required";
				return false;
			}

			if (isset($parameters['status'])) {
				if ($this->_valid_status($parameters['status'])) {
					$status = strtoupper($parameters['status']);
				} else {
					$this->_error = "Invalid status";
					return false;
				}
			}
			else
				$status = 'NEW';

			$product = new Product($parameters['product_id']);
			if (! $product->id) {
				$this->_error = "Product not found";
				return false;
			}

			// check if title set, required in database
			if (empty($parameters['title'])) {
				$this->_error = "Please enter a title";
				return false;
			}

			if (isset($parameters['requested_id']) && is_numeric($parameters['requested_id'])) {
				$requestor = new \Register\Customer($parameters['requested_id']);
				if (! $requestor->id) {
					$this->_error = "Requestor not found";
					return false;
				}
			}
			else {
				$this->_error = "Requestor id required";
				return false;
			}

			if (get_mysql_date($parameters['date_added'])) {
				$date_added = get_mysql_date($parameters['date_added']);
			}
			else {
				$date_added = date('Y-m-d H:i:s');
			}
			
			$add_object_query = "
				INSERT
				INTO	engineering_tasks
				(		code,title,type,status,date_added,requested_id,product_id,prerequisite_id)
				VALUES
				(		?,?,?,?,?,?,?,?)
			";

            $rs = executeSQLByParams($add_object_query,array($code,$parameters['title'],$type,$status,$date_added,$parameters['requested_id'],$product->id,$prerequisite_id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Engineering::Task::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			// Bust Cache
			$cache_key = "engineering.task[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			$cache_item->delete();

			if (! is_numeric($this->id)) {
				$this->_error = "No tasks identified to update";
				return false;
			}
			$update_object_query = "
				UPDATE	engineering_tasks
				SET		id = id
			";

			$bind_params = array();

			if (isset($parameters['title'])) {
				$update_object_query .= ",
						title = ?";
				array_push($bind_params,$parameters['title']);
			}

			if (isset($parameters['estimate'])) {
				$update_object_query .= ",
						estimate = ?";
				array_push($bind_params,$parameters['estimate']);
			}

			if (isset($parameters['description'])) {
				$update_object_query .= ",
						description = ?";
				array_push($bind_params,$parameters['description']);
			}

			if (isset($parameters['status'])) {
				if ($this->_valid_status($parameters['status'])) {
					$update_object_query .= ",
						status = ?";
					array_push($bind_params,$parameters['status']);
				} else {
					$this->_error = "Invalid status";
					return false;
				}
			}

			if (isset($parameters['priority'])) {
				if ($this->_valid_priority($parameters['priority'])) {
					$update_object_query .= ",
						priority = ?";
					array_push($bind_params,$parameters['priority']);
				} else {
					$this->_error = "Invalid priority";
					return false;
				}
			}

			if (isset($parameters['type'])) {
				if ($this->_valid_type($parameters['type'])) {
					$update_object_query .= ",
						type = ?";
					array_push($bind_params,$parameters['type']);
				} else {
					$this->_error = "Invalid type '".$parameters['type']."'";
					return false;
				}
			}

			if (isset($parameters['date_added'])) {
				if (get_mysql_date($parameters['date_added'])) {
					$update_object_query .= ",
						date_added = ?";
					array_push($bind_params,get_mysql_date($parameters['date_added']));
				} elseif (strlen($parameters['date_added'])) {
					$this->_error = "Invalid date";
					return false;
				}
			}

			if (isset($parameters['date_due'])) {
				if (get_mysql_date($parameters['date_due'])) {
					$update_object_query .= ",
						date_due = ?";
					array_push($bind_params,get_mysql_date($parameters['date_due']));
				}
				elseif (strlen($parameters['date_due'])) {
					$this->_error = "Invalid due date";
					return false;
				}
			}

			if (isset($parameters['location'])) {
				$update_object_query .= "
						location = ?";
				array_push($bind_params,$parameters['location']);
			}

			if (isset($parameters['estimate'])) {
				if (is_numeric($parameters['estimate']))
					$update_object_query .= ",
						estimate = ".$parameters['estimate'];
				else {
					$this->_error = "Estimate must be numeric";
					return false;
				}
            }
			if (isset($parameters['release_id'])) {
				if (is_numeric($parameters['release_id'])) {
                    if ($parameters['release_id'] > 0) {
                        $update_object_query .= ", release_id = ".$parameters['release_id'];
                    } else {
                        $update_object_query .= ", release_id = NULL";
                    }
				} else {
					$this->_error = "Invalid release_id";
					return false;
				}
			}

			if (isset($parameters['product_id'])) {
				if (is_numeric($parameters['product_id']))
					$update_object_query .= ",
						product_id = ".$parameters['product_id'];
				else {
					$this->_error = "Invalid product_id";
					return false;
				}
			}
			
			if (isset($parameters['assigned_id'])) {
				$tech = new \Register\Customer($parameters['assigned_id']);
				if ($tech->id) {
					$update_object_query .= ",
						assigned_id = ".$tech->id;
				} else {
                    // allow for tasks to be assigned to "unassigned"
                    $update_object_query .= ",
						assigned_id = 0";
				}
			}
			
			if (isset($parameters['requested_id'])) {
				$tech = new \Register\Customer($parameters['requested_id']);
				if ($tech->id) {
					$update_object_query .= ",
						requested_id = ".$tech->id;
				}
				else {
					$this->_error = "Tech not found";
					return false;
				}
			}
			
            // allow for prerequisite to be passed or else set to NULL if none passed
			if (isset($parameters['prerequisite_id'])) {
				$task = new Task($parameters['prerequisite_id']);
				if ($task->id) {
					$update_object_query .= ",
						prerequisite_id = ".$task->id;
				}
				else {
                    $update_object_query .= ",
						prerequisite_id = NULL";
				}
			}
			
			if (isset($parameters['project_id'])) {
				$project = new \Engineering\Project($parameters['project_id']);
				if ($project->id) {
					$update_object_query .= ",
						project_id = ".$project->id;
				}
				elseif ($parameters['project_id'] == '') {
					$update_object_query .= ",
						project_id = NULL
					";
				}
				else {
					$this->_error = "Project not found";
					return false;
				}
			}

			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

            $rs = executeSQLByParams($update_object_query, $bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Engineering::Task::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			return $this->details();
		}

		public function get($code) {

			$get_object_query = "
				SELECT	id
				FROM	engineering_tasks
				WHERE	code = ?
			";
			
            $rs = executeSQLByParams($get_object_query, array($code));
			if (! $rs) {
				$this->_error = "SQL Error in Engineering::Task::get(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			if ($id) {
				$this->id = $id;
				return $this->details();
			} else {
				return false;
			}
		}
		
		public function details() {
			$cache_key = "engineering.task[".$this->id."]";
			$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			if ($cache->error) {
				app_log("Error in cache mechanism: ".$cache->error,'error',__FILE__,__LINE__);
			}

			# Cached Object, Yay!
			if ($object = $cache->get()) {
				app_log($cache_key." found in cache",'trace');
				$this->_cached = true;
			}
			else {
				$get_object_query = "
					SELECT	*,
							unix_timestamp(date_added) timestamp_added
					FROM	engineering_tasks
					WHERE	id = ?
				";
				
				$rs = executeSQLByParams($get_object_query, array($this->id));
				if (! $rs) {
					$this->_error = "SQL Error in Engineering::Task::details(): ".$GLOBALS['_database']->ErrorMsg();
					return false;
				};
	
				$object = $rs->FetchNextObject(false);
				$this->id = $object->id;
				$this->_cached = false;
			}
			$this->code = $object->code;
			$this->title = $object->title;
			$this->description = $object->description;
			$this->date_added = $object->date_added;
			$this->date_due = $object->date_due;
			$this->status = $object->status;
			$this->type = $object->type;
			$this->estimate = $object->estimate;
			$this->location = $object->location;
			$this->release_id = $object->release_id;
			$this->product_id = $object->product_id;
			$this->requested_id = $object->requested_id;
			$this->assigned_id = $object->assigned_id;
			$this->priority = $object->priority;
			$this->timestamp_added = $object->timestamp_added;
            $this->project_id = $object->project_id;
            $this->prerequisite_id = $object->prerequisite_id;

			if (! $this->_cached) {
				// Cache Object
				app_log("Setting cache key ".$cache_key,'debug',__FILE__,__LINE__);
				if ($object->id) $result = $cache->set($object);
				app_log("Cache result: ".$result,'trace',__FILE__,__LINE__);	
			}

			$prereq = new \Engineering\Task($object->prerequisite_id);
			if ($prereq->id && $prereq->status != 'COMPLETE') $this->status = 'BLOCKED';
			return true;
		}
		public function prerequisite() {
			$task = new \Engineering\Task($this->prerequisite_id);
			return $task;
		}
		public function release() {
			return new Release($this->release_id);
		}

		public function product() {
			return new Product($this->product_id);
		}

		public function requestedBy() {
			return new \Register\Person($this->requested_id);
		}

		public function assignedTo() {
			return new \Register\Customer($this->assigned_id);
		}

		public function project() {
			return new \Engineering\Project($this->project_id);
		}

		public function assignTo($person_id) {
			$person = new \Register\Customer($person_id);
			if ($person->id) {
				$update_task_query = "
				UPDATE	engineering_tasks
				SET		assigned_id = ?
				WHERE	id = ?
				";

                $rs = executeSQLByParams($update_task_query, array($person->id,$this->id));
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->_error = "SQL Error in Engineering::Task::assignTo(): ".$GLOBALS['_database']->ErrorMsg();
					return false;
				}

				return $this->details();
			}
			else {
				$this->_error = "Person not found";
				return false;
			}
		}

		public function setStatus($status) {
			if ($this->_valid_status($status)) {
				$update_object_query = "
					UPDATE	engineering_tasks
					SET		status = ?
					WHERE	id = ?
				";
				
				$rs = executeSQLByParams($update_object_query, $update_object_query, array($status,$this->id));
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->_error = "SQL Error in Engineering::Task::setStatus(): ".$GLOBALS['_database']->ErrorMsg();
					return false;
				}
				else return true;
			}
			else {
				$this->_error = "Invalid Status";
				return false;
			}
		}

		public function error() {
			return $this->_error;
		}
		private function _valid_status($string) {
			if (preg_match('/^(new|hold|active|cancelled|testing|broken|complete)$/i',$string)) return true;
			else return false;
		}

		private function _valid_type($string) {
			if (preg_match('/^(feature|bug|test)$/i',$string)) return true;
			else return false;
		}

		private function _valid_priority($string) {
			if (preg_match('/^(normal|important|urgent|critical)$/i',$string)) return true;
			else return false;
		}
	}
