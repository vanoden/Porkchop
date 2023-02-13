<?php
	namespace Register\Organization;

	class OwnedProduct Extends \BaseClass {
        public $error;
		private $organization_id;
		private $product_id;
		private $quantity;

		public function __construct($org_id,$product_id) {
			$this->_tableName = 'register_organization_products';
			$this->organization_id = $org_id;
			$this->product_id = $product_id;
		}
	
        public function add($quantity,$parameters=array()) {
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
			app_log("Adding $quantity of product ".$this->product_id." for organization ".$this->organization_id,'notice',__FILE__,__LINE__);
            $GLOBALS['_database']->Execute(
				$add_product_query,
				array($this->organization_id,
					  $this->product_id,
					  $quantity,
					  $quantity
				)
			);
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
                return null;
            }
            return $this->details();
		}

        public function consume($quantity = 1) {
			$on_hand = $this->count();
			if ($quantity > $on_hand) {
				$this->error = "Less than $quantity available";
				return null;
			}
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
					$this->organization_id,
					$this->product_id
				)
			);
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
                return null;
            }
            return $this->details();
        }
		public function count() {
			$this->details();
			return $this->quantity;
		}
        public function details(): bool {
            $get_details_query = "
                SELECT  organization_id,
						product_id,
						quantity
                FROM    register_organization_products
                WHERE   organization_id = ?
				AND		product_id = ?
            ";

            $rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($this->organization_id,$this->product_id)
			);
            if (! $rs) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
                return false;
            }
			$object = $rs->FetchNextObject(false);
			if (!empty($object->id)) {
				$this->organization_id = $object->organization_id;
				$this->product_id = $object->product_id;
				$this->quantity = $object->quantity;
				app_log("Organization ".$this->organization()->name." has ".$this->quantity." of ".$this->product()->code,'trace');
			}
			else {
				$this->quantity = 0;
			}
			return true;
        }

		public function organization() {
			return new \Register\Organization($this->organization_id);
		}

		public function product() {
			return new \Product\Item($this->product_id);
		}
    }
