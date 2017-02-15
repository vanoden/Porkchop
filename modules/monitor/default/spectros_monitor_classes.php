<?php
	#######################################################
	### spectros_monitor.php							###
	### Spectros specific instance of the RootSeven		###
	### monitor class.  Mainly just extensions of new	###
	### classes to maintain compatibility.				###
	### A. Caravello 6/16/2013							###
	#######################################################
	
	# Load Monitor Classes
    require_once '/home/php_api/classes/monitor.php';

	# Definitions to Maintain Backward Compatibility to Spectros Monitor Applications
	class Monitor extends MonitorAsset
	{
	}

	class Hub extends MonitorSensor
	{
	}

	class Event extends MonitorCollection
	{
	}

	class Calibration
	{
		public $id;
		public $date_request;
		public $date_expires;
		public $date_confirm;
		public $custom_1;
		public $custom_2;
		public $custom_3;
		public $custom_4;
		public $custom_5;
		public $custom_6;

		public function __construct($id=0)
		{
			$this->id = $id;
		}
		public function catalog($parameters)
		{
			$ok_params = array(
				"code"	=> "c.code",
				"monitor"	=> "m.serial_number"
			);

			$get_parameters_query = "
				SELECT	c.id `code`
				FROM	monitor.calibrations c,
						monitor.monitors m
				WHERE	c.monitor_id = m.monitor_id
			";

			# Loop Through Params
			if (is_array($parameters))
			{
				foreach ($parameters as $parameter => $value)
				{
					if (! $ok_params[$parameter]) continue;
					if (! $parameters[$parameter]) continue;
					$get_parameters_query .= "
					AND		".$ok_params[$parameter]." = '".$parameters[$parameter]."'
					";
				}
			}
			$rs = $GLOBALS['_database']->Execute($get_parameters_query);
			if ($rs->ErrorMsg)
			{
				$this->error = $rs->ErrorMsg;
				return 0;
			}
			$calibrations = array();
			while ($calibration = $rs->FetchRow())
			{
				$item = new Calibration($calibration["code"]);
				array_push($calibrations,$item->details());
			}
			return $calibrations;
		}
		public function details()
		{
			$get_details_query = "
				SELECT	date_format(c.date_request,'%c/%e/%Y') date_request,
						unix_timestamp(c.date_request) timestamp_request,
						c.custom_1,
						c.custom_2,
						c.custom_3,
						c.custom_4,
						c.custom_5,
						c.custom_6
				FROM	monitor.calibrations c
				WHERE	c.id = '".$this->id."'
			";

			$rs = $GLOBALS['_database']->Execute($get_details_query);
			if ($rs->ErrorMsg())
			{
				$this->error = $rs->ErrorMsg();
				return 0;
			}
			else
			{
				return $rs->FetchRow();
			}
		}
		public function add($id,$params)
		{
			# Insert New Calibration Record
			$insert_calibration_query = "
				INSERT
				INTO	monitor.calibrations
				(		monitor_id,
						date_request,
						customer_id,
						custom_1,
						custom_2,
						custom_3,
						custom_4,
						custom_5,
						custom_6
				)
				VALUES
				(		'".$params['id']."',
						'".$params['sql_date_event']."',
						'".$params['customer_id']."',
						'".$params['sql_text_1']."',
						'".$params['sql_text_2']."',
						'".$params['sql_text_3']."',
						'".$params['sql_text_4']."',
						'".$params['sql_text_5']."',
						'".$params['sql_text_6']."'
				)
			";
			$rs = $GLOBALS['_database']->Execute($insert_calibration_query);
			if ($rs->ErrorMsg())
			{
				$this->error = $rs->ErrorMsg();
				return 0;
			}
			else
			{
				return $GLOBALS['_database']->Insert_ID();
			}
		}
	}

?>