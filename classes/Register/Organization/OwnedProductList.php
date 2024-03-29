<?php
	namespace Register\Organization;

	class OwnedProductList Extends \BaseListClass {
        public function find($parameters = array()) {
			$this->clearError();
			$this->resetCount();

            $get_objects_query = "
                SELECT  organization_id,product_id
                FROM    register_organization_products
                WHERE   product_id = product_id
            ";
            if (preg_match('/^\d+$/',$parameters['product_id']))
                $get_objects_query .= "
                AND     product_id = ".$parameters['product_id'];

            if (! $GLOBALS['_SESSION_']->customer->can('manage customers')) {
                if (preg_match('/^\d+/',$GLOBALS['_customer']->organization->id))
                    $parameters['organization_id'] = $GLOBALS['_customer']->organization->id;
                else
                    $parameters['organization_id'] = 0;
            }
            if (preg_match('/^\d+$/',$parameters['organization_id']))
                $get_objects_query .= "
                AND     organization_id = ".$parameters['organization_id'];

            $rs = $GLOBALS['_database']->Execute($get_objects_query);
            if (! $rs) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
                return null;
            }

            $objects = array();

            while (list($organization_id,$product_id) = $rs->FetchRow()) {
                $orgProduct = new \Register\Organization\OwnedProduct($organization_id,$product_id);
                $object = $orgProduct;
                if ($this->error()) {
                    $this->error("Error getting details for OrganizationOwnedProduct: ".$this->error());
                    return null;
                }
                array_push($objects,$object);
            }

            return $objects;
        }
	}
