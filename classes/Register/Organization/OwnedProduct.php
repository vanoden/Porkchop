<?php
	namespace Register\Organization;

	class OwnedProduct {
        public $error;
		private $organization;
		private $product;
		private $quantity;

		public function __construct($org_id,$product_id) {
			$this->organization = new \Register\Organization($org_id);
			if ($this->organization->error) {
				$this->error = "Error loading organization: ".$this->organization->error;
				return null;
			}
			if (! $this->organization->id) {
				$this->error = "Organization not found";
				return null;
			}
			$this->product = new \Product\Item($product_id);
			if ($this->product->error) {
				$this->error = "Error loading product: ".$this->product->error;
				return null;
			}
			if (! $this->product->id) {
				$this->error = "Product not found";
				return null;
			}
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
			app_log("Adding $quantity of product ".$this->product->id." for organization ".$this->organization->id,'notice',__FILE__,__LINE__);
            $GLOBALS['_database']->Execute(
				$add_product_query,
				array($this->organization->id,
					  $this->product->id,
					  $quantity,
					  $quantity
				)
			);
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->error = "SQL Error in Register::Organization::Products::::add:".$GLOBALS['_database']->ErrorMsg();
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
					$this->organization->id,
					$this->product->id
				)
			);
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->error = "SQL Error in Register::Organization::Products::consume():".$GLOBALS['_database']->ErrorMsg();
                return null;
            }
            return $this->details();
        }
		public function count() {
			$this->details();
			return $this->quantity;
		}
        private function details() {
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
				array($this->organization->id,$this->product->id)
			);
            if (! $rs) {
                $this->error = "SQL Error in Register::Organization::Products::::details(): ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }
			$object = $rs->FetchNextObject(false);
			$this->organization = new \Register\Organization($object->organization_id);
			$this->product = new \Product\Item($object->product_id);
			$this->quantity = $object->quantity;

			app_log("Organization ".$this->organization->name." has ".$this->quantity." of ".$this->product->code,'trace');
			return 1;
        }
    }
?>