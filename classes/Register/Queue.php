<?php
	namespace Register;

	class Queue Extends \BaseModel {

		public $code;
		public $status;
		public $is_reseller;
		public $assigned_reseller_id;
		public $notes;
		public $register_user_id;
		public $product_id;
		public $serial_number;
		public $date_created;
		public $organization_id;
		
		// business contact fields
		public $name;		
		public $address;
		public $city;
		public $state;
		public $zip;
		public $phone;
		public $cell;
		private $possibleOrganizationStatus = array('NEW','ACTIVE','EXPIRED','HIDDEN','DELETED');

		public function __construct($id = 0) {
			$this->_tableName = 'register_queue';
			$this->_addStatus(array('VERIFYING','PENDING','APPROVED','DENIED'));
			parent::__construct($id);
		}

		public function get($login): bool {
			$this->clearError();

			$customer = new \Register\Customer();
			if (!$customer->get($login)) {
				$this->error("Customer not found");
				return false;
			}

			$database = new \Database\Service();

			$get_object_query = "
				SELECT	id
				FROM	register_queue
				WHERE	register_user_id = ?
			";
			$database->AddParam($customer->id);
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function getByQueuedLogin($queuedUserId = 0) {
			if (!empty($queuedUserId)) {

				$get_queued_contacts_query = "
					SELECT	id
					FROM	register_queue
					WHERE	register_user_id = " . $queuedUserId;
					
				$rs = $GLOBALS['_database']->Execute( $get_queued_contacts_query );
				if (! $rs) {
					$this->SQLError($GLOBALS['_database']->ErrorMsg());
					return null;
				}
				list($id) = $rs->FetchRow();
				$this->id = $id;
				return $this->details();
			}
		}
		
		/**
		 * update potential customer
		 * @param array $parameters
		 */
		public function update ($parameters = []): bool {
			if (! preg_match('/^[0-9]+$/',$this->id)) {
				$this->error("ID Required for update method.");
				return false;
			}

			$bind_params = array();
			$update_contact_query = " UPDATE register_queue SET id = id";

			if (isset($parameters['notes'])) {
				$update_contact_query .= ",
						notes = ?";
				array_push($bind_params,$parameters['notes']);
			}

			if (isset($parameters['status'])) {
				if (! $this->validStatus($parameters['status'])) {
					$this->error("Invalid Status for RegisterQueue entry");
					return false;
				}
				$update_contact_query .= ",
						status = ?";
				array_push($bind_params,$parameters['status']);
			}

			$update_contact_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);
			query_log($update_contact_query);
			$GLOBALS['_database']->Execute($update_contact_query,$bind_params);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
						
			// return basic queue entry details
			return $this->details();
		}
		
		/**
		 * sync the live account that may or may not be associated with the queued account being edited
		 */
		public function syncLiveAccount () {
			// if they've found an existing organization
			if(!empty($_REQUEST['organization'])) $this->name = $_REQUEST['organization'];

			// process the new or existing queued customer to the chosen status
			global $_config;
			$registerOrganizationList = new \Register\OrganizationList();          
			list($organization) = $registerOrganizationList->find(array('name' => $this->name, 'status' => $this->possibleOrganizationStatus));
			if (!empty($organization)) {
				app_log("Organization ".$organization->name." matched");
				$organization->update(array('status' => 'ACTIVE','notes' => $this->notes));
			}
			else {
				app_log("Creating organization ".$this->name);
				$organization = new \Register\Organization();
				$organization->add(
					array(
						'name' => $this->name,
						'code' => $this->code,
						'status' => 'NEW',
						'is_reseller' => $this->is_reseller,
						'assigned_reseller_id' => $this->assigned_reseller_id,
						'notes' => $this->notes
					)
				);
				if ($organization->error()) {
					$this->SQLError($organization->error());
					return false;
				}
			}
			$this->organization_id = $organization->id;

			// update to have the queued login match the 'approved' organization
			$customer = new \Register\Customer($this->register_user_id);
			app_log("Assigning customer ".$customer->login." to organization ".$organization->name);
			$customer->update(array('organization_id' => $organization->id));
			
			// they've entered a product, add a support_request record with customer id, date entry and request items for each item entered
			if (!empty($this->product_id)) {
				app_log("Adding transfer of ownership request for ".$this->product_id);
				$supportRequest = new \Support\Request();
				$supportRequest->add(
					array(
						"date_request"	    => date("Y-m-d H:i:s"),
						"customer_id"	    => $this->register_user_id,
						"organization_id"   => $organization->id,

						"type"			    => 'service',
						"status"		    => "NEW"
					)
				);
				if ($supportRequest->error()) {
					$this->error("Error adding support request: ".$supportRequest->error());
					return false;
				}
				
				$item = array (
					'line'			=> 1,
					'product_id'    => $this->product_id,
					'description'	=> "Approve registration of new device",
					'quantity'		=> 1
				);
				if (!empty($this->serial_number)) $item['serial_number'] = $this->serial_number;
				$ticket = $supportRequest->addItem($item);
				if ($ticket) {
					// Add Action to Ticket
					$action = $ticket->addAction(array(
						'requested_id'	=> $customer->id,
						'status'		=> 'NEW',
						'type'			=> 'Transfer Ownership',
						'description'	=> 'Confirm and Transfer ownership to new company'
						)
					);

					// Notify Admins of Action
					$template = new \Content\Template\Shell($GLOBALS['_config']->support->unassigned_action->template);
					$template->addParams(array(
						'TICKET.NUMBER'	=> $ticket->ticketNumber(),
						'TICKET.LINK'	=> $ticket->internalLink(),
						'ACTION.LINK'	=> $action->internalLink(),
						'ACTION.TYPE'	=> $action->type,
						'ACTION.DESCRIPTION'	=> $action->description
					));
					$message = new \Email\Message();
					$message->html(true);
					$message->from($GLOBALS['_config']->support->unassigned_action->from);
					$message->subject($GLOBALS['_config']->support->unassigned_action->subject);
					$message->body($template->content());
					$privilege = new \Register\Privilege();
					if ($privilege->get('get support notifications')) {
						app_log("Notifying Support Team");
						$privilege->notify($message);
					}
				}
				else {
					app_log("Error creating ticket: ".$supportRequest->error(),'error');
				}
			}

			// Notify Customer of Approval
			$template = new \Content\Template\Shell($GLOBALS['_config']->register->account_activation_notification->template);
			$message = new \Email\Message();
			$message->html(true);
			$message->from($GLOBALS['_config']->register->account_activation_notification->from);
			$message->subject($GLOBALS['_config']->register->account_activation_notification->subject);
			$message->body($template->content());
			if ($customer->notify($message)) {
				app_log("Activation notice sent");
			}
			else {
				app_log("Activation notice failed: ".$customer->error(),'error');
				$this->error("Error notifying customer: ".$customer->error());
			}
		}

		// hydrate known details about this queue object from known id if set
		public function details(): bool {
			if (empty($this->id)) {
				$this->error("ID Required for details method.");
				return false;
			}
			
			$get_queued_contacts_query = "
				SELECT	*
				FROM	register_queue
				WHERE	id = " . $this->id;
			$rs = $GLOBALS['_database']->Execute( $get_queued_contacts_query );
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if (!empty($object->id)) {
				$this->id = $object->id;
				$this->name = $object->name;
				$this->address = $object->address;
				$this->city = $object->city;
				$this->state = $object->state;
				$this->zip = $object->zip;
				$this->phone = $object->phone;
				$this->cell = $object->cell;
				$this->code = $object->code;
				$this->status = $object->status;
				$this->date_created = $object->date_created;
				$this->notes = $object->notes;
				$this->product_id = $object->product_id;
				$this->serial_number = $object->serial_number;
				$this->register_user_id = $object->register_user_id;
				$this->is_reseller = $object->is_reseller;
				$this->assigned_reseller_id = $object->assigned_reseller_id;
				app_log("Found registration ".$this->id);
			}
			else {
				$this->name = null;
				$this->address = null;
				$this->city = null;
				$this->state = null;
				$this->zip = null;
				$this->phone = null;
				$this->cell = null;
				$this->code = null;
				$this->status = null;
				$this->date_created = null;
				$this->is_reseller = null;
				$this->assigned_reseller_id = null;
				$this->notes = null;
				$this->product_id = null;
				$this->serial_number = null;
				$this->register_user_id = null;
				app_log("No registration for ".$this->id);
				$this->id = null;
			}
			return true;
		}

		public function asset() {
			$asset = new \Product\Instance();
			if ($asset->getWithProduct($this->product_id,$this->serial_number)) return $asset;
			else {
				$this->error("Asset not found");
				return null;
			}
		}

		public function product() {
			return new \Product\Item($this->product_id);
		}

		public function customer() {
			return new \Register\Customer($this->register_user_id);
		}

		public function getVerificationURL() {
			$customer = new \Register\Customer($this->id);
			return "/_register/validate?login=".$customer->login."&validation_key=".$customer->validationKey();
		}

		/**
		 * add new potential customer
		 * @param array $parameters
		 */
		public function add($parameters = []) {		
			app_log("Register::Queue::add()",'trace',__FILE__,__LINE__);
			$this->clearError();
			$add_object_query = "
				INSERT
				INTO	register_queue
					(name, code, date_created, is_reseller, assigned_reseller_id, address, city, state, zip, product_id, serial_number, register_user_id)
				VALUES
					(?, ?, sysdate(), ?, ?, ?, ?, ?, ?, ?, ?, ?)
					";

			// zero out empty values for int DB fields
			if (empty($parameters['is_reseller'])) $parameters['is_reseller'] = 0;
			if (empty($parameters['assigned_reseller_id'])) $parameters['assigned_reseller_id'] = 0;
			if (empty($parameters['product_id'])) $parameters['product_id'] = 0;
			if (empty($parameters['register_user_id'])) $parameters['register_user_id'] = NULL;

			$rs = $GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['name'],
					$parameters['code'],
					$parameters['is_reseller'],
					$parameters['assigned_reseller_id'],
					$parameters['address'],
					$parameters['city'],
					$parameters['state'],
					$parameters['zip'],
					$parameters['product_id'],
					$parameters['serial_number'],
					$parameters['register_user_id']
				)
			);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->id;
		}

		public function organization() {
			return new \Register\Organization($this->organization_id);
		}
	}
