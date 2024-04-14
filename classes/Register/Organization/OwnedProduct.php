<?php
	namespace Register\Organization;

	class OwnedProduct Extends \BaseClass {

		private $organization_id;
		private $product_id;
		private $quantity;

		public function __construct($org_id,$product_id) {
			$this->organization_id = $org_id;
			$this->product_id = $product_id;
			if ($this->organization_id > 0 && $this->product_id > 0) $this->details();
		}
	
        public function add($parameters = []) {

			$this->clearError();
			$organization = new \Register\Organization($this->organization_id);
			if ($organization->id < 1) {
				$this->error("Organization not found");
				return false;
			}
			$product = new \Product\Item($this->product_id);
			if ($product->id < 1) {
				$this->error("Product not found");
				return false;
			}

			$database = new \Database\Service();

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
			app_log("Adding ".$parameters["quantity"]." of product ".$this->product_id." for organization ".$this->organization_id,'notice',__FILE__,__LINE__);
			$database->AddParam($organization->id);
			$database->AddParam($product->id);
			$database->AddParam($parameters['quantity']);
			$database->AddParam($parameters['quantity']);

            $database->Execute($add_product_query);
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
                return null;
            }
			
            // audit the add event
            $auditLog = new \Site\AuditLog\Event();
            $auditLog->add(array(
                'instance_id' => $this->id,
                'description' => 'Added new '.$this->_objectName(),
                'class_name' => get_class($this),
                'class_method' => 'add'
            ));

            return $this->details();
		}

        public function consume($quantity = 1) {

			$on_hand = $this->count();
			if ($quantity > $on_hand) {
				$this->error("Less than $quantity available");
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

			if (!empty($object->organization_id)) {
				$this->organization_id = $object->organization_id;
				$this->product_id = $object->product_id;
				$this->quantity = $object->quantity;
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
