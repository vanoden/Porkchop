<?php
	namespace Resource;

	class Privilege extends \BaseClass {
		// Entity Type
		public $entity_type;

		// Entity ID
		public int $entity_id;

		// Read
		public bool $read = false;
		
		// Write
		public bool $write = false;

		/**
		 * Constructor
		 * @param mixed $entity_type 
		 * @param int $entity_id 
		 * @param bool $read 
		 * @param bool $write 
		 * @return void 
		 */
		public function __construct($entity_type = null, $entity_id = -1, $read = false, $write = false) {
			$this->entity_type = $entity_type;
			if (!empty($entity_id)) $this->entity_id = (int)$entity_id;
			if (!empty($read)) $this->read = $read;
			if (!empty($write)) $this->write = $write;
		}

		/**
		 * Entity Type Name
		 * @return string 
		 */
		public function entity_type_name(): string {
			switch ($this->entity_type) {
				case 'a':
					return "All";
					break;
				case 'u':
					return "User";
					break;
				case 'r':
					return "Role";
					break;
				case 'o':
					return "Organization";
					break;
				default:
					return "Unknown";
					break;
			};
		}

		/**
		 * Entity Code
		 * @return string 
		 */
		public function entity_code(): string {
			switch ($this->entity_type) {
				case 'a':
					return 'All';
					break;
				case 'u':
					$entity = new \Register\Customer($this->entity_id);
					return $entity->code;
					break;
				case 'r':
					$entity = new \Register\Role($this->entity_id);
					return $entity->name;
					break;
				case 'o':
					$entity = new \Register\Organization($this->entity_id);
					return $entity->code;
					break;
				default:
					return "Unknown";
					break;
			};
		}
		/**
		 * Entity Name
		 * @return mixed 
		 */
		public function entity_name() {
			switch ($this->entity_type) {
				case 'a':
					return 'All';
					break;
				case 'u':
					$entity = new \Register\Customer($this->entity_id);
					return $entity->login;
					break;
				case 'r':
					$entity = new \Register\Role($this->entity_id);
					return $entity->name;
					break;
				case 'o':
					$entity = new \Register\Organization($this->entity_id);
					return $entity->name;
					break;
				default:
					return "Unknown";
					break;
			};
		}

		/**
		 * Access Level Name
		 * @return string 
		 */
		public function access_level_name(): string {
			if ($this->write && $this->read) return "Read/Write";
			elseif ($this->write) return "Write Only";
			elseif ($this->read) return "Read Only";
			else return "None";
		}
	};