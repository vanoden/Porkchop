<?php
	namespace Bench;

	class Build extends \BaseModel {
		public $product_id;
		public $number;
		public $timestamp;
		public $status;
		public $message;

		public function __construct($id = 0) {
            $this->_tableName = 'bench_builds';
			if (preg_match('/^\d+$/', $id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = []) {
			if (isset($parameters['product_code'])) {
				$product = new \Bench\Product();
				if ($product->get($parameters['product_code'])) {
					$product_id = $product->id;
				}
				else {
					$this->_error = "Product not found";
					return false;
				}
			} else {
				$this->_error = "Product code required";
				return false;
			}

			$add_object_query = "
				INSERT
				INTO	bench_builds
				(		id,product_id,number,timestamp,status,message)
				VALUES
				(		null,?,?,sysdate(),'NEW',?)
			";

			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$product_id,
					$parameters['number'],
					$parameters['message']
				)
			);

			// audit the add event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));
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

		public function update($parameters = []): bool {
			
			$update_object_query = "
				UPDATE	bench_builds
				SET		status = ?, message = ?
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute(
				$update_object_query,
				array(
					$parameters['status'],
					$parameters['message'],
					$this->id
				)
			);

			// audit the add event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));
			return $this->details();
		}

		public function details(): bool  {
			$get_details_query = "
				SELECT	*
				FROM	bench_builds
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_details_query,array($this->id)
			);

			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			if ($object = $rs->FetchNextObject(false)) {
				$this->id = $object->id;
				$this->product_id = $object->product_id;
				$this->number = $object->number;
				$this->timestamp = $object->timestamp;
				$this->status = $object->status;
				$this->message = $object->message;
			}
			else {
				$this->id = 0;
			}
			return 1;
		}

		public function callAPI($request) {
			$result = shell_exec($GLOBALS['_config']->service.' --uri="'.$request."'");
			$response = new \HTTP\Response();
			$response->parse($result);
			return $response;
		}
	}
