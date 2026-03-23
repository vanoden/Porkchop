<?php
	namespace Network;

	class Subnet Extends \BaseModel {
		public $address;				// Base Network address of the subnet in long format
		public $size = 1;				// Size of the subnet as an integer
		public $type;					// Type of subnet: ipv4 or ipv6
		public $description;			// Optional description of the subnet
		public $managed = 'AUTO';		// How this subnet is managed: AUTO (automatically managed by the system), MANUAL (manually managed by an administrator)
		public $risk_level = 0;			// Risk level of the subnet (-100 to 100)
		public $date_added;				// Date the subnet was added to the system
		public $date_last_seen;			// Last time a connection was seen from an address in this subnet
		public $uri_last_seen;			// The URI that was last accessed from an address in this subnet
		public $applied_risk_level = 0;	// The last risk level that was applied to this subnet
		public $last_session_id;		// ID of the last session that was associated with this subnet when its risk level was updated

		public function __construct($id = 0) {
			$this->_tableName = 'network_subnets';
			$this->_addFields(array(
				'address',
				'size',
				'type',
				'description',
				'managed',
				'risk_level',
				'date_added',
				'date_last_seen',
				'uri_last_seen',
				'applied_risk_level',
				'last_session_id'

			));
			parent::__construct($id);
		}

		public function add($params = array()) {
			$database = new \Database\Service();
			if (preg_match('/^ipv4$/i', $params['type'])) {
				if (! filter_var($params['address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
					$this->error("Invalid ".$params['type']." address");
					return false;
				}
				$params['type'] = 'ipv4';
				$params['address'] = ip2long($params['address']);
				if ($params['size'] < 1 || $params['size'] > 32) {
					$this->error("Invalid size for IPv4 subnet");
					return false;
				}
			}
			elseif (preg_match('/^ipv6$/i', $params['type'])) {
				if (! filter_var($params['address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
					$this->error("Invalid ".$params['type']." address");
					return false;
				}
				$params['type'] = 'ipv6';
				$params['address'] = inet_pton($params['address']);
				if ($params['size'] < 1 || $params['size'] > 128) {
					$this->error("Invalid size for IPv6 subnet");
					return false;
				}
			}
			elseif (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $params['address'], $matches)) {
				$ip = $matches[0];
				$size = 1;
				if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
					$this->error("Invalid ".$params['type']." address");
					return false;
				}
				if ($size < 1 || $size > 32) {
					$this->error("Invalid size for IPv4 subnet");
					return false;
				}
				$params['type'] = 'ipv4';
				$params['address'] = ip2long($ip);
				$params['size'] = $size;
			}
			elseif (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\/(\d{1,2})$/', $params['address'], $matches)) {
				$ip = $matches[0];
				$cidrsize = (int)$matches[5];
				$size = 2^(32 - $cidrsize);
				if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
					$this->error("Invalid ".$params['type']." address");
					return false;
				}
				if ($size < 1 || $size > 32) {
					$this->error("Invalid size for ".$params['type']." subnet");
					return false;
				}
				$params['type'] = 'ipv4';
				$params['address'] = ip2long($ip);
				$params['size'] = $size;
			}
			elseif (preg_match('/^([a-fA-F0-9:]+)\/(\d{1,3})$/', $params['address'], $matches)) {
				$ip = $matches[1];
				$cidrsize = (int)$matches[2];
				$size = 2^(128 - $cidrsize);
				if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
					$this->error("Invalid ".$params['type']." address");
					return false;
				}
				if ($size < 1 || $size > 128) {
					$this->error("Invalid size for ".$params['type']." subnet");
					return false;
				}
				$params['type'] = 'ipv6';
				$params['address'] = inet_pton($ip);
				$params['size'] = $size;
			}
			else {
				$this->error("Type required for address '".$params['address']."'");
				return false;
			}

			if (empty($params['address'])) {
				$this->error("Address is required");
				return false;
			}

			if (preg_match('/^ipv4$/i', $params['type'])) {
				if (is_numeric($params['address']) && $params['address'] > 0 && $params['address'] < 4294967295) {
					$params['address'] = (string)$params['address'];
				}
				elseif (! filter_var($params['address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
					$this->error("Invalid ".$params['type']." address: ".$params['address']);
					return false;
				}
				else $params['address'] = ip2long($params['address']);
			}
			elseif (preg_match('/^ipv6$/i', $params['type'])) {
				if (! filter_var($params['address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
					$this->error("Invalid ".$params['type']." address: ".$params['address']);
					return false;
				}
				$params['address'] = inet_pton($params['address']);
			}
			elseif (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\/(\d{1,2})$/', $params['address'], $matches)) {
				$ip = $matches[0];
				$cidrsize = (int)$matches[5];
				$size = 2^(32 - $cidrsize);
				if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
					$this->error("Invalid address");
					return false;
				}
				if ($size < 1 || $size > 32) {
					$this->error("Invalid size for IPv4 subnet");
					return false;
				}
				$params['type'] = 'ipv4';
				$params['address'] = ip2long($ip);
				$params['size'] = $size;
			}
			elseif (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $params['address'], $matches)) {
				$ip = $matches[0];
				$size = 1;
				if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
					$this->error("Invalid address");
					return false;
				}
				if ($size < 1 || $size > 32) {
					$this->error("Invalid size for IPv4 subnet");
					return false;
				}
				$params['type'] = 'ipv4';
				$params['address'] = ip2long($ip);
				$params['size'] = $size;
			}
			elseif (preg_match('/^([a-fA-F0-9:]+)\/(\d{1,3})$/', $params['address'], $matches)) {
				$ip = $matches[1];
				$cidrsize = (int)$matches[2];
				$size = 2^(128 - $cidrsize);
				if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
					$this->error("Invalid address");
					return false;
				}
				if ($size < 1 || $size > 128) {
					$this->error("Invalid size for IPv6 subnet");
					return false;
				}
				$params['type'] = 'ipv6';
				$params['address'] = inet_pton($ip);
				$params['size'] = $size;
			}
			elseif (!empty($params['address']) && is_numeric($params['address'])) {
				$params['address'] = (string)$params['address'];
			}
			else {
				$this->error("Invalid address format");
				return false;
			}

			if (!is_numeric($params['size']) || $params['size'] < 1 || (preg_match('/^ipv4$/i', $params['type']) && $params['size'] > 32) || (preg_match('/^ipv6$/i', $params['type']) && $params['size'] > 128)) {
				$this->error("Invalid size");
				return false;
			}

			if (isset($params['description'])) {
				$params['description'] = noXSS(trim($params['description']));
			}

			if (isset($params['risk_level'])) {
				if (! is_numeric($params['risk_level']) || $params['risk_level'] < -100 || $params['risk_level'] > 100) {
					$this->error("Invalid risk level");
					return false;
				}
			}

			if (!empty($params['managed']) && preg_match('/^manual$/i', $params['managed'])) {
				$params['managed'] = 'MANUAL';
			}
			else {
				$params['managed'] = 'AUTO';
			}

			$database->AddParam($params['address']);
			$database->AddParam($params['size']);
			$database->AddParam($params['type']);

			$add_object_query = "
				INSERT
				INTO	network_subnets
				(		address,
						size,
						type,
						date_added,
						date_last_seen
				)
				VALUES
				(		?,?,?,
						sysdate(),
						sysdate()
				)
			";

			$database->Execute($add_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$this->id = $database->Insert_ID();

			// add audit log
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			return $this->upgrade($params);
		}

		public function getByCIDR($cidr) {
			return $this->details();
		}

		/** @method public update()
		 * Updates the subnet with the given parameters.
		 * @param array $params The parameters to update.
		 * - address: The new base network address of the subnet (can be in standard notation or long format)
		 * - size: The new size of the subnet as an integer
		 * - type: The new type of the subnet (ipv4 or ipv6)
		 * - description: An optional new description of the subnet
		 * - risk_level: The new risk level of the subnet as an integer between -100 and 100
		 * - managed: How the subnet is managed (AUTO or MANUAL)
		 * @return bool True if the update was successful, false otherwise.
		 */
		public function update($params = []): bool {
			// Clear Errors
			$this->clearErrors();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Update Query
			$update_object_query = "
				UPDATE	network_subnets
				SET		date_last_seen = sysdate()";

			// Validate and Append Parameters to Query
			if (isset($params['type'])) {
				if (! $this->validType($params['type'])) {
					$this->error("Invalid type '".$params['type']."'");
					return false;
				}
				else {
					$update_object_query .= ",
					type = ?";
					$database->AddParam($params['type']);
				}
			}
			if (!empty($params['size'])) {
				if (! is_numeric($params['size'])) {
					$this->error("Invalid size");
					return false;
				}
			}

			if (isset($params['description'])) {
				$update_object_query .= ",
					description = ?";
				$database->AddParam(noXSS(trim($params['description'])));
			}

			if (!empty($params['risk_level'])) {
				if (! is_numeric($params['risk_level']) || $params['risk_level'] < -100 || $params['risk_level'] > 100) {
					$this->error("Invalid risk level");
					return false;
				}
				else {
					$update_object_query .= ",
					risk_level = ?";
					$database->AddParam($params['risk_level']);
				}
			}

			if (!empty($params['managed'])) {
				if (preg_match('/^manual$/i', $params['managed'])) {
					$managed = 'MANUAL';
				}
				else {
					$managed = 'AUTO';
				}
				$update_object_query .= ",
					managed = ?";
				$database->AddParam($managed);
			}

			if (isset($params['address'])) {
				if (preg_match('/^ipv4$/i', $this->type)) {
					if (! $this->validipv4($params['address'])) {
						$this->error("Invalid ".$this->type."address");
						return false;
					}
					else {
						$params['address'] = ip2long($params['address']);
					}
				}
				elseif (preg_match('/^ipv6$/i', $this->type)) {
					if (! $this->validipv6($params['address'])) {
						$this->error("Invalid ".$this->type."address");
						return false;
					}
					else {
						$params['address'] = inet_pton($params['address']);
					}
				}

				$update_object_query .= ",
					address = ?";
				$database->AddParam($params['address']);
			}

			if (!empty($params['uri_last_seen'])) {
				if (! filter_var($params['uri_last_seen'], FILTER_VALIDATE_URL)) {
					$this->error("Invalid URI for uri_last_seen");
					$url_last_seen = '[INVALID]';
				}
				$update_object_query .= ",
					uri_last_seen = ?";
				$database->AddParam($params['uri_last_seen']);
			}

			if (!empty($params['applied_risk_level'])) {
				if (! is_numeric($params['applied_risk_level']) || $params['applied_risk_level'] < -100 || $params['applied_risk_level'] > 100) {
					$this->error("Invalid applied risk level");
					return false;
				}
				else {
					$update_object_query .= ",
					applied_risk_level = ?";
					$database->AddParam($params['applied_risk_level']);
				}
			}

			if (!empty($params['last_session_id'])) {
				if (! is_numeric($params['last_session_id'])) {
					$this->error("Invalid last session ID");
					return false;
				}
				else {
					$update_object_query .= ",
					last_session_id = ?";
					$database->AddParam($params['last_session_id']);
				}
			}

			$update_object_query .= "
				WHERE	id = ?
			";
			$database->AddParam($this->id);

			$database->Execute($update_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			return $this->details();
		}

		/** @method public delete()
		 * Deletes the subnet from the database.
		 * @return bool True if the deletion was successful, false otherwise.
		 */
		public function delete(): bool {
			
			$database = new \Database\Service();
			$delete_object_query = "
				DELETE
				FROM	network_subnets
				WHERE	id = ?
			";
			$database->AddParam($this->id);
			$database->Execute($delete_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// audit the delete event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Deleted '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'delete'
			));	

			return true;
		}

		/** @method public seen()
		 * Updates the date_last_seen field to the current date and time. This should be called whenever a connection is seen from an address in this subnet.
		 */
		public function seen(): void {
			$database = new \Database\Service();
			$update_seen_query = "
				UPDATE	network_subnets
				SET		date_last_seen = NOW(),
						uri_last_seen = ?
				WHERE	id = ?
			";
			$database->AddParam($_SERVER['REQUEST_URI'] ?? null);
			$database->AddParam($this->id);
			$database->Execute($update_seen_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
			}
		}

		/** @method maintain(new_risk_level)
		 * Maintains the subnet's risk level by updating it to the new risk level provided. This should be called whenever there is a change in the subnet's risk level to ensure that the database is updated accordingly.
		 */
		public function maintain($new_risk_level, $applied_risk_level): void {
			$database = new \Database\Service();
			$update_risk_query = "
				UPDATE	network_subnets
				SET		risk_level = ?,
						date_last_seen = sysdate(),
						uri_last_seen = ?,
						applied_risk_level = ?,
						last_session_id = ?
				WHERE	id = ?
			";
			$database->AddParam($new_risk_level);
			$database->AddParam($_SERVER['REQUEST_URI'] ?? null);
			$database->AddParam($applied_risk_level);
			$database->AddParam($GLOBALS['_SESSION_']->id ?? null);
			$database->AddParam($this->id);
			$database->Execute($update_risk_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
			}
			else {
				$this->risk_level = $new_risk_level;
			}
		}

		/** @method public details()
		 * Retrieves the details of the subnet from the database and updates the object's properties.
		 */
		public function details(): bool {
			$database = new \Database\Service();
			$get_object_query = "
				SELECT	*
				FROM	network_subnets
				WHERE	id = ?
			";
			$database->AddParam($this->id);
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$object = $rs->FetchNextObject(false);
			if ($object) {
				$this->risk_level = $object->risk_level;
				$this->address = $object->address;
				$this->size = $object->size;
				$this->type = $object->type;
				$this->description = $object->description;
				$this->managed = $object->managed;
				$this->date_added = $object->date_added;
				$this->date_last_seen = $object->date_last_seen;
				$this->uri_last_seen = $object->uri_last_seen;
				$this->applied_risk_level = $object->applied_risk_level;
				$this->last_session_id = $object->last_session_id;
				return true;
			}
			else {
				$this->id = null;
				return false;
			}
		}

		/** @method public riskLevel()
		 * Returns the risk level of the current subnet as an integer between -100 and 100.
		 */
		public function riskLevel(): int {
			return $this->risk_level;
		}

		/** @method public adjustRiskLevel(int $control)
		 * Use an algorithm to adjust the risk level of the subnet based on a new risk contribution value. The contribution value can be positive or negative, and the algorithm will adjust the risk level accordingly while keeping it within the bounds of -100 and 100.
		 * @param int $control The new risk contribution value to adjust the risk level by.
		 * @return int The new adjusted risk level after applying the contribution.
		 */
		public function adjustRiskLevel(int $control): int {
			$risk_level = 0;
			if ($control > $this->risk_level) {
				$risk_level += (int)(($control - $this->risk_level) / 4);
			}
			else if ($control < $this->risk_level) {
				$risk_level -= (int)(($this->risk_level - $control) / 10);
			}
			if ($risk_level > 100) $risk_level = 100;
			if ($risk_level < -100) $risk_level = -100;
			app_log("Adjusting risk level of subnet ID ".$this->id." from ".$this->risk_level." to ".$risk_level, 'debug');
			$this->maintain($risk_level, $control);
			return $risk_level;
		}

		/** @method public realAddress()
		 * Returns the real address of the subnet in standard notation (dotted quad for IPv4, colon-separated for IPv6).
		 */
		public function realAddress(): string {
			if (preg_match('/^ipv4$/i', $this->type)) {
				if ($this->size == 1) {
					return long2ip($this->address);
				}
				else {
					$mask = ~((1 << (32 - $this->size)) - 1);
					return long2ip($this->address & $mask)."/".$this->size;
				}
			}
			elseif (preg_match('/^ipv6$/i', $this->type)) {
				return inet_ntop($this->address);
			}
			else return '';
		}

		/** @method public session()
		 * Returns the session associated with the last session ID that was recorded for this subnet when its risk level was updated. This can be used to retrieve information about the session that contributed to the current risk level of the subnet.
		 * @return \Session The session object associated with the last session ID recorded for this subnet, or null if there is no session or an error occurs.
		 */
		public function session(): ?\Site\Session {
			if (empty($this->last_session_id)) {
				return null;
			}
			$session = new \Site\Session($this->last_session_id);
			if ($session->id) {
				return $session;
			}
			return null;
		}
	}
