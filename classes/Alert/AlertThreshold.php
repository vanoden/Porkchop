<?php
	namespace Alert;

	class AlertThreshold extends \ORM\BaseModel {

		public $id;
		public $sensor_id;
		public $operator;
		public $value;
		public $sensorValue;
		public $tableName = 'alert_threshold';
        public $fields = array('id','sensor_id','operator', 'value');

        public function check($value='') {
           $this->sensorValue = $value;
           if (!$this->isValueOK()) $this->cacheAlertTriggered();
        }

        public isValueOK() {
            $valueIsOK = true;
            switch ($this->operator) {
                case ">":
                    if ($this->sensorValue <= $this->value) $valueIsOK = false;                    
                    break;
                case "<":
                    if ($this->sensorValue >= $this->value) $valueIsOK = false;
                    break;
                case "=":
                    if ($this->sensorValue != $this->value) $valueIsOK = false;
                    break;
            }
            return $valueIsOK;
        }

        public function cacheAlertTriggered() {
            $alert_threshold_key = "sensor_threshold_alert[".$this->id."]";
            $alert_threshold_cache_item = new \Cache\Item($GLOBALS['_CACHE_'], $alert_threshold_key);        
            $alert_threshold_cache_item->set(getAlertTriggeredMsg());
        }
        
        public getAlertTriggeredMsg() {
            $msg = new stdClass();
            $msg->status = 'Alert Threshold Exceeded';
            $msg->message = $this->sensorValue . ' is not ' . $this->operator . ' threshold value';
            $msg->sensor_id = $this->sensor_id;
            $msg->operator = $this->operator;
            $msg->threshold_value = $this->value;
            $msg->sensor_value = $this->sensorValue;
            return json_encode($msg);
        }        
	}
