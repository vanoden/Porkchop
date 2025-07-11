<?php
namespace Media;

class Item extends \BaseModel {
	public function __construct($id = 0) {
		$this->_tableName = 'media_items';
		parent::__construct($id);
	}

	public function add($parameters = []) {

		# Some Things Required
		if (! $parameters['type']) {
			$this->error("type required for new MediaItem");
			return null;
		}
		# Generate 'unique' code if none provided
		if (! $parameters["code"]) {
			$parameters["code"] = uniqid($parameters["type"] . '-');
		}
		$add_object_query = "
				INSERT
				INTO	media_items
				(		id,
						type,
						date_created,
						owner_id,
						code
				)
				VALUES
				(		null,?,sysdate(),?,?)
			";
		$GLOBALS['_database']->Execute(
			$add_object_query,
			array(
				$parameters["type"],
				$GLOBALS['_SESSION_']->customer->id,
				$parameters["code"]
			)
		);
		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return null;
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

		return $this->update($this->id, $parameters);
	}

	public function update($parameters = array()): bool {

		foreach ($parameters as $label => $value) {
			app_log("Setting meta '$label' = '$value'", 'debug', __FILE__, __LINE__);
			$this->setMeta($this->id, $label, $value);
		}
		$update_object_query = "
				UPDATE	media_items
				SET		date_updated = sysdate()
				WHERE	id = ?
			";
		$GLOBALS['_database']->Execute(
			$update_object_query,
			array($this->id)
		);

		// audit the update event
		$auditLog = new \Site\AuditLog\Event();
		$auditLog->add(array(
			'instance_id' => $this->id,
			'description' => 'Updated ' . $this->_objectName(),
			'class_name' => get_class($this),
			'class_method' => 'update'
		));

		return $this->details();
	}
	public function find($parameters = array()) {
		$find_object_query = "
				SELECT	distinct(m.item_id)
				FROM	media_metadata m,
						media_items i
				WHERE	m.item_id = i.id
				AND		i.deleted = 0
			";
		foreach ($parameters as $label => $value) {
			if (! preg_match('/^[\w\-\.\_]+$/', $label)) {
				$this->error("Invalid parameter name in MediaItem::find()");
				return null;
			}
			if ($label == "type")
				$find_object_query .= "
					AND	i.type = " . $GLOBALS['_database']->qstr($value, get_magic_quotes_gpc());
			else
				$find_object_query .= "
					AND (	m.label = '" . $label . "'
						AND m.value = " . $GLOBALS['_database']->qstr($value, get_magic_quotes_gpc()) . "
					)";
		}
		app_log("Query: $find_object_query", 'debug', __FILE__, __LINE__);
		$rs = $GLOBALS['_database']->Execute($find_object_query);
		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return null;
		}
		$objects = array();
		while (list($id) = $rs->FetchRow()) {
			$object = $this->details();
			if (is_object($object)) {
				$privileges = $this->privileges($id);
				if ($privileges['read']) {
					app_log("Adding " . $object->id . " to array", 'debug', __FILE__, __LINE__);
					array_push($objects, $object);
				} else {
					app_log("Hiding " . $object->id . " lacking privileges", 'debug', __FILE__, __LINE__);
				}
			}
		}
		return $objects;
	}
	public function get($code) {
		$get_object_query = "
				SELECT	id
				FROM	media_items
				WHERE	code = ?
			";
		$rs = $GLOBALS['_database']->Execute(
			$get_object_query,
			array($code)
		);
		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return null;
		}
		list($id) = $rs->FetchRow();
		$this->id = $id;
		return $this->details();
	}
	public function details(): bool {
		$get_object_query = "
				SELECT	id,
						type,
						date_created,
						date_updated,
						owner_id,
						code
				FROM	media_items
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
		$array = $rs->FetchRow();
		if (! $array['id']) return (object) $array;
		$metadata = $this->getMeta($this->id);
		$array = array_merge($array, $metadata);

		$filelist = new FileList();
		$images = $filelist->find(array("item_id" => $this->id));
		$array['files'] = $images;
		return true;
	}
	public function getMeta($id) {
		# Get Metadata
		$get_metadata_query = "
				SELECT	label,
						value
				FROM	media_metadata
				WHERE	item_id = ?
			";
		$rs = $GLOBALS['_database']->Execute(
			$get_metadata_query,
			array($id)
		);
		if (! $rs) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return null;
		}
		$array = array();
		while (list($label, $value) = $rs->FetchRow()) {
			$array[$label] = $value;
		}
		return $array;
	}
	public function setMeta($id, $parameter, $value) {
		$add_metadata_query = "
				INSERT
				INTO	media_metadata
				(		item_id,
						label,
						value
				)
				VALUES
				(		?,?,?)
				ON DUPLICATE KEY UPDATE
						value = ?
			";
		$GLOBALS['_database']->Execute(
			$add_metadata_query,
			array(
				$id,
				$parameter,
				$value,
				$value
			)
		);
		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return null;
		}
		return array($parameter, $value);
	}
	public function privileges($media_id, $customer_id = null, $organization_id = null) {
		if (! $GLOBALS['_SESSION_']->customer->can('manage media files')) {
			$customer_id = $GLOBALS['_SESSION_']->customer->id;
			$organization_id = $GLOBALS['_SESSION_']->customer->organization()->id;
		}
		if ($customer_id === null || ! preg_match('/^\d+$/', $customer_id)) $customer_id = $GLOBALS['_SESSION_']->customer->id;
		if ($organization_id === null || ! preg_match('/^\d+$/', $organization_id)) $organization_id = $GLOBALS['_SESSION_']->customer->organization()->id;
		if ($customer_id === null || ! preg_match('/^\d+$/', $customer_id)) $customer_id = 0;
		if ($organization_id === null || ! preg_match('/^\d+$/', $organization_id)) $organization_id = 0;

		app_log("Checking privileges for item " . $media_id . ", customer " . $customer_id . ", organization " . $organization_id, 'debug', __FILE__, __LINE__);

		$get_privileges_query = "
				SELECT	`read`,`write`
				FROM	media_privileges
				WHERE	customer_id = ?
				AND		item_id = ?
				UNION
				SELECT	`read`,`write`
				FROM	media_privileges
				WHERE	organization_id = ?
				AND		item_id = ?
				UNION
				SELECT	`read`,`write`
				FROM	media_privileges
				WHERE	customer_id = ?
				AND		item_id = 0
				UNION
				SELECT	`read`,`write`
				FROM	media_privileges
				WHERE	organization_id = ?
				AND		item_id = 0
				UNION
				SELECT	`read`,`write`
				FROM	media_privileges
				WHERE	customer_id = 0
				AND		organization_id = 0
				AND		item_id = ?
				UNION
				SELECT	`read`,`write`
				FROM	media_privileges
				WHERE	organization_id = 0
				AND		customer_id = 0
				AND		item_id = 0
				LIMIT 1
			";
		$rs = $GLOBALS['_database']->Execute(
			$get_privileges_query,
			array(
				$customer_id,
				$media_id,
				$organization_id,
				$media_id,
				$media_id,
				$customer_id,
				$organization_id
			)
		);
		if (! $rs) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return null;
		}
		list($read, $write) = $rs->FetchRow();

		app_log("Privileges for item " . $media_id . ": read => " . $read . ", write => " . $write, 'debug', __FILE__, __LINE__);
		return array("read" => $read, "write" => $write);
	}
}
