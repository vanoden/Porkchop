<?php
namespace Purchase;

class Order extends \BaseModel {
	public $code;
	public $date_created;

	public function __construct($id = 0) {
		if (!empty($id)) {
			$this->id = $id;
			$this->details();
		}
	}

	public function add($params = array()) {

		if (!empty($params['vendor_contact'])) {
			$user = new \Register\Person();
			if (!$user->get($params['vendor_contact'])) {
				$this->error("User not found");
				return false;
			}
			$params['user_id'] = $user->id;
			$params['organization_id'] = $user->organization_id;
		} elseif (!empty($params['vendor'])) {
			$vendor = new \Register\Organization();
			if (!$vendor->get($params['vendor'])) {
				$this->error("Vendor not found");
				return false;
			}
			$params['vendor_id'] = $vendor->id;
			$params['vendor_contact_id'] = $vendor->accounts_contact();
		} else {
			$this->error("vendor or vendor_contact required");
			return false;
		}

		if (empty($params['status']))
			$params['status'] = 'OPEN';
		if (empty($params['date_created']))
			$params['date_created'] = get_mysql_date('now');
		elseif (!get_mysql_date($params['date_created'])) {
			$this->error("Invalid date_created");
			return false;
		}

		$bind_params = array(
			$GLOBALS['_SESSION_']->customer->id,
			get_mysql_date($params['date_created']),
			$params['user_id'],
			$params['organization_id'],
			$params['status']
		);

		$add_object_query = "
				INSERT
				INTO	purchase_orders
						user_created,
						date_created,
						vendor_contact_id,
						vendor_id,
						status
				VALUES  (?,?,?,?,?)
			";

		$GLOBALS['_database']->Execute($add_object_query, $bind_params);
		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->error("SQL Error in Purchase::Order->add(): " . $GLOBALS['_database']->ErrorMsg());
			return false;
		}
		$this->id = $GLOBALS['_database']->Insert_ID();

		// add audit log
		$auditLog = new \Site\AuditLog\Event();
		$auditLog->add(array(
			'instance_id' => $this->id,
			'description' => 'Added new ' . $this->_objectName(),
			'class_name' => get_class($this),
			'class_method' => 'add'
		));

		return $this->update($params);
	}

	public function update($params = array()): bool {
		$update_object_query = "
				UPDATE	purchase_orders
				SET		id = id";
		$bind_params = array();

		if (!empty($params['status'])) {
			$update_object_query .= ",
						status = ?";
			array_push($bind_params, $params['status']);
		}
		return $this->details();
	}

	public function get($code) {
		$get_object_query = "
				SELECT	id
				FROM	purchase_orders
				WHERE	code = ?
			";
		$rs = $GLOBALS['_database']->Execute($get_object_query, array($code));
		if (!$rs) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return false;
		}
		list($result) = $rs->FetchRow();
		if ($result > 0) {
			$this->id = $result;

			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));			
			
			return $this->details();
		} else {
			$this->error("WorkInvoice not found");
			return false;
		}
	}

	public function details(): bool {
		$get_object_query = "
				SELECT	*
				FROM	purchase_orders
				WHERE	id = ?
			";
		$rs = $GLOBALS['_database']->Execute($get_object_query, array($this->id));
		if (!$rs) {
			$this->error("SQL Error in Purchase::Order::details(): " . $GLOBALS['_database']->ErrorMsg());
			return false;
		}
		if ($rs->record_count < 1) {
			$this->id = null;
			$this->code = null;
			$this->date_created = null;
			return true;
		} else {
			$rs->FetchNextObject(false);
			$this->id = $rs->id;
			$this->code = $rs->code;
			$this->date_created = $rs->date_created;
			return true;
		}
	}

	public function receivePayment($params) {
		$params['invoice_id'] = $this->id;
		$payment = new \Purchase\Order\Payment();
		if (!$payment->add($params)) {
			$this->error($payment->error());
			return 0;
		} else {
			return $payment->id;
		}
	}

	public function payments($params) {
		$paymentList = new \Purchase\Order\PaymentList();
		return $paymentList->find($params);
	}
}
