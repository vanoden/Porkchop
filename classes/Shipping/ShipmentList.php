<?php
	namespace Shipping;

	class ShipmentList Extends \BaseListClass {
	
		public function __construct() {
			$this->_modelName = '\Shipping\Shipment';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$find_objects_query = "
				SELECT	id
				FROM	shipping_shipments
				WHERE	id = id";

			$bind_params = array();

			// Load the Model Class for Field Validations
			$validationClass = new \Shipping\Shipment();

			if (is_numeric($parameters['document_id'])) {
				$find_objects_query .= "
				AND		document_id = ?";
				array_push($bind_params,$parameters['document_id']);
			}

			if (is_numeric($parameters['rec_contact_id'])) {
				$find_objects_query .= "
				AND		rec_contact_id = ?";
				array_push($bind_params,$parameters['rec_contact_id']);
			}

			if (is_numeric($parameters['send_contact_id'])) {
				$find_objects_query .= "
				AND		send_contact_id = ?";
				array_push($bind_params,$parameters['send_contact_id']);
			}

            if (is_numeric($parameters['rec_location_id'])) {
                $find_objects_query .= "
                AND     rec_location_id = ?";
                array_push($bind_params,$parameters['rec_location_id']);
            }

            if (is_numeric($parameters['send_location_id'])) {
                $find_objects_query .= "
                AND     send_location_id = ?";
                array_push($bind_params,$parameters['send_location_id']);
            }
			
			if (isset($parameters['status']) && $validationClass->validStatus($parameters['status'])) {
				$find_objects_query .= "
				AND		status = ?";
				array_push($bind_params,$parameters['status']);
			}

			if (!empty($controls['sort']) && $validationClass->hasField($controls['sort'])) {
				$find_objects_query .= "
					ORDER BY ".$controls['sort'];
				if (preg_match('/^(asc|desc)$/i',$controls['order']))
					$find_objects_query .= " ".$controls['order'];
			}
			else {
				$find_objects_query .= "
				ORDER BY	date_entered DESC";
			}

			if (is_numeric($controls['limit'])) {
				$find_objects_query .= "
				LIMIT	".$controls['limit'];
				if (is_numeric($controls['offset'])) {
					$find_objects_query .= "
					OFFSET	".$controls['offset'];
				}
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return [];
			}
			
			$shipments = array();
			while (list($id) = $rs->FetchRow()) {
				if (! $controls['count']) {
					$shipment = new \Shipping\Shipment($id);
					array_push($shipments,$shipment);
				}
				$this->incrementCount();
			}
			return $shipments;
		}
		
		public function searchAdvanced($parameters, $advanced, $controls): array {
			return $this->findAdvanced($parameters, $advanced, $controls);
		}
	}
