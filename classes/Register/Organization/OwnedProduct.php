<?php
	namespace Register\Organization;

	class OwnedProduct {
        public $error;

        public function add($organization_id,$product_id,$quantity,$parameters=array())
        {
            $add_product_query = "
                INSERT
                INTO    register_organization_products
                (       organization_id,
                        product_id,
                        quantity
                )
                VALUES
                (       ?,
                        ?,
                        ?
                )
                ON DUPLICATE KEY
                UPDATE
                        quantity = quantity + ?
            ";
            $GLOBALS['_database']->Execute(
				$add_product_query,
				array($organization_id,
					  $product_id,
					  $quantity,
					  $quantity
				)
			);
            if ($GLOBALS['_database']->ErrorMsg())
            {
                $this->error = "SQL Error in OrganizationProducts::add:".$GLOBALS['_database']->ErrorMsg();
                return 0;
            }
            return 1;
        }

        public function consume($organization_id,$product_id,$quantity = 1)
        {
            $use_product_query = "
                UPDATE  register_organization_products
                SET     quantity = quantity - ?
                WHERE   organization_id = ?
                AND     product_id = ?
            ";

            $GLOBALS['_database']->Execute(
				$use_product_query,
				array(
					$quantity,
					$organization_id,
					$product_id
				)
			);
            if ($GLOBALS['_database']->ErrorMsg())
            {
                $this->error = "SQL Error in OrganizationProducts::use:".$GLOBALS['_database']->ErrorMsg();
                return 0;
            }
            return $this->details($organization_id,$product_id);
        }

        public function get($organization_id,$product_id)
        {
            $get_object_query = "
                SELECT  organization_id,product_id
                FROM    register_organization_products
                WHERE   organization_id = ?
				AND		product_id = ?
            ";
            if (! role('register manager'))
            {
                $organization_id = $GLOBALS['_SESSION_']->customer->organization->id;
			}
            $rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array(
					$organization_id,
					$product_id
				)
			);
            if (! $rs)
            {
                $this->error = "SQL Error in OrganizationOwnedProduct::get: ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }

            $objects = array();

            list($organization_id,$product_id) = $rs->FetchRow();

            $object = $this->details($organization_id,$product_id);
            if ($this->error)
            {
                $this->error = "Error getting details for OrganizationOwnedProduct: ".$this->error;
                return null;
			}
            return $object;
        }
        public function find($parameters = array())
        {
            $get_objects_query = "
                SELECT  organization_id,product_id
                FROM    register_organization_products
                WHERE   product_id = product_id
            ";
            if (preg_match('/^\d+$/',$parameters['product_id']))
                $get_objects_query .= "
                AND     product_id = ".$parameters['product_id'];

            if (! role('register manager'))
            {
                if (preg_match('/^\d+/',$GLOBALS['_customer']->organization->id))
                    $parameters['organization_id'] = $GLOBALS['_customer']->organization->id;
                else
                    $parameters['organization_id'] = 0;
            }
            if (preg_match('/^\d+$/',$parameters['organization_id']))
                $get_objects_query .= "
                AND     organization_id = ".$parameters['organization_id'];

            $rs = $GLOBALS['_database']->Execute($get_objects_query);
            if (! $rs)
            {
                $this->error = "SQL Error in OrganizationOwnedProduct::find: ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }

            $objects = array();

            while (list($organization_id,$product_id) = $rs->FetchRow())
            {
                $object = $this->details($organization_id,$product_id);
                if ($this->error)
                {
                    $this->error = "Error getting details for OrganizationOwnedProduct: ".$this->error;
                    return null;
                }
                array_push($objects,$object);
            }

            return $objects;
        }

        private function details($organization_id,$product_id)
        {
            $get_details_query = "
                SELECT  organization_id,product_id,quantity
                FROM    register_organization_products
                WHERE   organization_id = ?
				AND		product_id = ?
            ";

            $rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($organization_id,$product_id)
			);
            if (! $rs)
            {
                $this->error = "SQL Error in OrganizationOwnedProducts::details: ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }
            return $rs->FetchNextObject(false);
        }
    }
?>