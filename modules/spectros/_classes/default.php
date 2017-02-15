<?
	require_module("monitor");

	class SpectrosCollection extends MonitorCollection {
		public function getCTValue($collection_id,$sensor_id) {
			# Get Collection
			$collection = $this->details($collection_id);
			
			# Get Readings for Calibration Sensor
			$readings = $this->readings($collection_id,$sensor_id);
			
			# Loop Through Readings in Order and Calculate Value
			$prev_value = 0;
			$prev_time = $collection->timestamp_start;
			$ct = 0;
			foreach ($readings as $reading) {
				if (isset($prev_time)) {
					$avg_value = ($reading->value + $prev_value) / 2;
					$length = $reading->timestamp - $prev_time;
					$ct += ($avg_value * $length)/3600;
					#error_log("AVG: $avg_value LEN: $length CT: $ct");

					$prev_time = $reading->timestamp;
					$prev_value = $reading->value;
				}
			}
			return sprintf("%0.2f",$ct);
		}
	}
	class SpectrosMonitor extends MonitorAsset {
		
	}
	class SpectrosSensor extends MonitorSensor {
		
	}
	class CalibrationVerificationCredit {
		public $error;
		public $calibration_id;
		public $product_id;

		public function __construct() {
			# Database Initialization
			$schema = new SpectrosSchema();
			if ($schema->error) {
				$this->error = $schema->error;
			}

			# Get Product ID for Calibration Verification
			require_once(MODULES."/product/_classes/default.php");
			$_product = new Product();
			$product = $_product->get('CalibrationVerificationCredit');
			$this->product_id = $product->id;
		}

		# Get available credits
		public function get($organization_id) {
			$this->error = '';

			$_orgProduct = new OrganizationOwnedProduct();
			if ($_orgProduct->error) {
				$this->error = "Error initializing credits: ".$_orgProduct->error;
				return null;
			}

			$credit = $_orgProduct->get(
				$organization_id,
				$this->product_id
			);

			if ($_orgProduct->error) {
				$this->error = "Error getting credits: ".$_orgProduct->error;
				return null;
			}
			return $credit;
		}
		# Get available credits
		public function find($parameters = array()) {
			$this->error = '';
			$_orgProduct = new OrganizationOwnedProduct();
			$credit = $_orgProduct->find($parameters);
			if ($_orgProduct->error) {
				$this->error = "Error finding credits: ".$_orgProduct->error;
				return null;
			}
			return $credit;
		}

		# Add to available credits
		public function add($organization_id,$quantity = 1) {
			$this->error = '';
			$_orgProduct = new OrganizationOwnedProduct();
			$credit = $_orgProduct->add(
				$organization_id,
				$this->product_id,
				$quantity
			);
			if ($_orgProduct->error) {
				$this->error = "Error adding credits: ".$_orgProduct->error;
				return null;
			}
			return $credit;
		}

		# Decrement available credits
		public function consume($organization_id,$quantity = 1) {
			$this->error = '';
			$_orgProduct = new OrganizationOwnedProduct();
			$credits = $_orgProduct->consume($organization_id,$this->product_id,$quantity);
			if ($_orgProduct->error) {
				$this->error = "Error using credits: ".$_orgProduct->error;
				return null;
			}
			return $credits;
		}

		# See How Many Credits are Available
		public function count($organization_id = 0) {
			if (! $organization_id || ! role('register manager'))
				$organization_id = $GLOBALS['_SESSION_']->customer->organization->id;
			
			$this->error = '';
			$_orgProduct = new OrganizationOwnedProduct();

			$records = $_orgProduct->find(
				array(
					"organization_id"	=> $organization_id,
					"product_id"		=> $this->product_id,
				)
			);
			if ($_orgProduct->error) {
				$this->error = "Error counting credits: ".$_orgProduct->error;
				return null;
			}
			$credits = 0;
			foreach ($records as $record) {
				$credits += $record->quantity;
			}
			return $credits;
		}
	}

	class CalibrationVerification {
		public $error;
		
		public function __construct() {
			# Database Initialization
			$schema = new SpectrosSchema();
			if ($schema->error) {
				$this->error = $schema->error;
			}
		}
		public function add($parameters=array()) {
			if (! $parameters['code']) {
				$parameters['code'] = uniqid();
			}
			$parameters['date_request'] = get_mysql_date($parameters['date_request']);
			if (! $parameters['date_request']) $parameters['date_request'] = date('Y-m-d H:i:s');

			# Confirmation expires in 1 year
			$expires = sprintf("%s-%s",substr($parameters['date_request'],0,4) + 1,substr($parameters['date_request'],5));

			$add_object_query = "
				INSERT
				INTO	monitor_calibrations
				(		code,
						asset_id,
						customer_id,
						date_request,
						date_expires
				)
				VALUES
				(		?,?,?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['code'],
					$parameters['asset_id'],
					$GLOBALS['_SESSION_']->customer->id,
					$parameters['date_request'],
					$expires
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in CalibrationVerification::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$id = $GLOBALS['_database']->Insert_ID();

			return $this->details($id);
		}
		public function next($asset_id) {
			$get_object_query = "
				SELECT	id
				FROM	monitor_calibrations
		        WHERE	asset_id = ?
				AND		date_expires > sysdate()
				AND		date_confirm = '0000-00-00 00:00:00'
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array(
					$asset_id
				)
			);
			if (! $rs) {
				$this->error = "SQL Error in CalibrationVerification::next: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			return $this->details($id);
		}
		public function confirm($id) {
			$update_object_query = "
				UPDATE	monitor_calibrations
				SET		date_confirm = sysdate()
				WHERE	id = ?
			";
			$GLOBALS['_database']->Execute(
				$update_object_query,
				array(
					$id
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in CalibrationVerification::confirm: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->details($id);
		}
		public function find($parameters=array()) {
			$get_object_query = "
				SELECT	mc.id
				FROM	monitor_calibrations mc,
						register_users ru,
						monitor_assets ma
				WHERE	ru.id = mc.customer_id
				AND		mc.asset_id = ma.asset_id
			";

			if (isset($parameters['organization_id']) && preg_match('/^\d+$/',$parameters['organization_id'])) {
				$get_object_query .= "
				AND		ru.organization_id = ".$parameters['organization_id'];
			}
			if (isset($parameters['date_start']) && $parameters['date_start'] && get_mysql_date($parameters['date_start'])) {
				$get_object_query .= "
				AND		mc.date_request >= '".get_mysql_date($parameters['date_start'])."'";
			}
			elseif (isset($parameters['date_start']) && $parameters['date_start']) {
				$this->error = "Invalid start date";
				return null;
			}
			if (isset($parameters['date_end']) && $parameters['date_end'] && get_mysql_date($parameters['date_end'])) {
				$get_object_query .= "
				AND		mc.date_request <= '".get_mysql_date($parameters['date_end'])."'";
			}
			elseif (isset($parameters['date_end']) && $parameters['date_end']) {
				$this->error = "Invalid end date";
				return null;
			}
			if (isset($parameters['product_id']) && preg_match('/^\d+$/',$parameters['product_id'])) {
				$get_object_query .= "
				AND		ma.product_id = ".$parameters['product_id'];
			}
			if (preg_match('/^\d+$/',$parameters['asset_id'])) {
				$get_object_query .= "
				AND		mc.asset_id = ".$parameters['asset_id'];
			}
			elseif($parameters['asset_id']) {
				$this->error = "Invalid asset_id";
				return null;
			}

			$rs = $GLOBALS['_database']->Execute($get_object_query);
			if (! $rs) {
				$this->error = "SQL Error in CalibrationVerification::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = $this->details($id);
				array_push($objects,$object);
			}
			return $objects;
		}
		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	monitor_calibrations
		        WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array(
					$code
				)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in CalibrationVerification::get: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			return $this->details($id);
		}
		private function details($id) {
			$get_object_query = "
				SELECT	*
				FROM	monitor_calibrations
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($id)
			);
			if (! $rs)
			{
				$this->error = "SQL Error in CalibrationVerification::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$object = $rs->FetchNextObject(false);
			$metadata = $this->getMetadata($id);
			foreach ($metadata as $key => $value)
			{
				$object->$key = $value;
			}
			return $object;
		}
		public function setMetadata($id,$key,$value) {
			$set_object_query = "
				INSERT
				INTO	monitor_calibration_metadata
				(calibration_id,`key`,value)
				VALUES	(?,?,?)
				ON DUPLICATE KEY UPDATE
				VALUE = ?
			";
			$GLOBALS['_database']->Execute(
				$set_object_query,
				array($id,$key,$value,$value)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in MonitorCalibration::setMetadata: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}
		public function getMetadata($id) {
			$get_object_query = "
				SELECT	`key`,value
				FROM	monitor_calibration_metadata
				WHERE	calibration_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($id)
			);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->error = "SQL Error in MonitorCalibration::getMetadata: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$array = array();
			while (list($label,$value) = $rs->FetchRow())
			{
				$array[$label] = $value;
			}
			return $array;
		}
	}
	class SpectrosSchema {
		public $module = 'monitor';
		public $error;

		public function __construct() {
			$this->upgrade();
		}

		public function version() {
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array("monitor__info",$schema_list)) {
				# Create __info table
				$create_table_query = "
					CREATE TABLE monitor__info (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating info table in MediaInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					return null;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	monitor__info
				WHERE	label = 'schema_version_spectroscalibration'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs) {
				$this->error = "SQL Error in CalibrationInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			list($version) = $rs->FetchRow();
			return $version;
		}
		public function upgrade() {
			$current_schema_version = $this->version();

			if ($current_schema_version < 1) {
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `monitor_calibrations` (
						id				int(11) NOT NULL AUTO_INCREMENT,
						code			varchar(100) NOT NULL,
						asset_id		int(11) NOT NULL,
						customer_id		int(11) NOT NULL,
						date_request	datetime,
						date_confirm	datetime,
						PRIMARY KEY `PK_ID` (`id`),
						UNIQUE KEY `uk_code` (`code`),
						INDEX `IDX_ASSET_CUSTOMER` (`asset_id`,`customer_id`),
						FOREIGN KEY `FK_ASSET_ID` (`asset_id`) REFERENCES monitor_assets (`asset_id`),
						FOREIGN KEY `FK_CUSTOMER_ID` (`customer_id`) REFERENCES register_users (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating monitor_calibrations table in CalibrationInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	monitor__info
					VALUES	('schema_version_spectroscalibration',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in SpectrosSchema::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
			if ($current_schema_version < 2) {
				app_log("Upgrading schema to version 2",'notice',__FILE__,__LINE__);

				# Start Transaction
				if (! $GLOBALS['_database']->BeginTrans())
					app_log("Transactions not supported",'warning',__FILE__,__LINE__);

				$create_table_query = "
					CREATE TABLE IF NOT EXISTS `monitor_calibration_metadata` (
						`id`				int(11) NOT NULL AUTO_INCREMENT,
						`calibration_id`	int(11) NOT NULL,
						`key`				varchar(100) NOT NULL,
						`value`				varchar(100) NOT NULL,
						PRIMARY KEY `pk_calibration_metadata` (`id`),
						UNIQUE KEY `uk_calibration_metadata` (`calibration_id`,`key`),
						FOREIGN KEY `fk_calibration_id` (`calibration_id`) REFERENCES monitor_calibrations (`id`)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error creating monitor_calibration_metadata table in CalibrationInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}

				$current_schema_version = 2;
				$update_schema_version = "
					INSERT
					INTO	monitor__info
					VALUES	('schema_version_spectroscalibration',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in CalibrationInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return null;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
?>
