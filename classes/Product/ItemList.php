<?php
	namespace Product;

	class ItemList Extends \BaseListClass {

		public function __construct() {
			$this->_modelName = '\Product\Item';
		}

		/** @method getAllProducts(type = 'unique')
		 * Get Product ID's of All ACTIVE Products
		 * @params type Product Type to Match On
		 * @return array
		 */
		public function getAllProducts($type = 'unique') {
			// Clear Previous Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query to get Product IDs
			$query = "
				SELECT id
				FROM product_products
				WHERE status = 'ACTIVE'
			";

			if ($type !== null) {
				$query .= " AND type = ?";
				$database->AddParam($type);
			}
			$query .= " ORDER BY id";

			$rs = $database->Execute($query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}

			$productIds = array();
			while (list($id) = $rs->FetchRow()) {
				$productIds[] = $id;
			}

			return $productIds;
		}

		/** @method findAdvanced(parameters, controls, advanced)
		 * Find items based on a set of parameters, controls, and advanced search options
		 * @param array $parameters (fields to match on)
		 * @param array $controls (sort/limit/offset)
		 * @param array $advanced (advanced search parameters)
		 * @return array
		 */
		public function findAdvanced($parameters = [], $controls = [], $advanced = []): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();
	
			// For Validation
			$validationclass = new \Product\Item();

            // Build inner distinct ID query (no ORDER BY/LIMIT here)
            $find_ids_query = "
                SELECT DISTINCT p.id
                FROM product_products p
                LEFT OUTER JOIN product_relations r ON p.id = r.product_id
                WHERE p.status != 'DELETED'";

			if (!empty($parameters['search'])) {
				if (!$validationclass->validSearch($parameters['search']) ) {
					$this->error("Invalid Search String");
					return [];
				}
                $find_ids_query .= "
				AND     (
							p.code LIKE ?
							OR p.name LIKE ?
							OR p.description LIKE ?
						)";
				$search_string = $parameters['search'];

				// Specified Wildcards
				if (preg_match('/^\*/',$search_string) || preg_match('/\*$/',$search_string)) {
					$search_string = preg_replace('/^\*/','%',$search_string);
					$search_string = preg_replace('/\*$/','%',$search_string);
				} else {
					// Implied Wildcards
					$search_string = '%'.$parameters['search'].'%';
				}
                $database->AddParams([$search_string,$search_string,$search_string]);
			}

			# Filter on Given Parameters
			if (isset($parameters['type'])) {
				if (is_array($parameters['type'])) {
                    $find_ids_query .= "
					AND		p.type in (";
					$count = 0;
                    foreach ($parameters['type'] as $type) {
						if (!$validationclass->validType($type)) {
							$this->error("Invalid Type: ".$type);
							return [];
						}
                        if ($count) $find_ids_query .= ",";
						$count ++;
                        $find_ids_query .= "'".$type."'";
					}
                    $find_ids_query .= ")";
				}
				else {
					if (!$validationclass->validType($parameters["type"])) {
						$this->error("Invalid Type: ".$parameters["type"]);
						return [];
					}
                    $find_ids_query .= "
					AND		p.type = ?";
					$database->AddParam($parameters["type"]);
				}
			}
			if (isset($parameters['status']) && is_array($parameters['status'])) {
				// Validate all statuses first
				foreach ($parameters['status'] as $status) {
					if (! $validationclass->validStatus($status)) {
						$this->error("Invalid Status: ".$status);
						return [];
					}
				}
				// Add the IN clause once after validation
                $find_ids_query .= "
				AND     p.status IN ('".implode("','",$parameters['status'])."')";
			}
			elseif (!empty($parameters['status']) && $validationclass->validStatus($parameters['status'])) {
                $find_ids_query .= "
				AND		p.status = ?";
				$database->AddParam(strtoupper($parameters["status"]));
			}
			else
                $find_ids_query .= "
				AND		p.status = 'ACTIVE'";

			if (isset($parameters['category_code'])) {
				if (is_array($parameters['category_code'])) {
					$category_ids = array();
					foreach ($parameters['category_code'] as $category_code) {
						list($category) = $this->find(
							array(
								'code'	=> $category_code
							)
						);
						array_push($category_ids,$category->id);
					}
                    $find_ids_query .= "
					AND	r.parent_id in (".join(',',$category_ids).")";
				}
				elseif(preg_match('/^[\w\-\_\.\s]+$/',$parameters['category_code'])) {
					list($category) = $this->find(
						array(
							'code'	=> $parameters['category_code']
						)
					);
					$parameters['category_id'] = $category->id;
				}
			}

			if (isset($parameters['category'])) {

				// Get Parent ID
				$_parent = new \Product\Item($parameters["category"]);
				$category_id = $_parent->id;

				if (! $category_id) {
					$this->error("Invalid Category");
					return [];
				}
                $find_ids_query .= "
                AND		r.parent_id = ?";
				$database->AddParam($category_id);

            } elseif (isset($parameters['category_id'])) {
                $find_ids_query .= "
                AND		r.parent_id = ?";
				$database->AddParam($parameters['category_id']);
			}

			if (isset($parameters['id']) and preg_match('/^\d+$/',$parameters['id'])) {
                    $find_ids_query .= "
				AND		p.id = ?";
				$database->AddParam($parameters['id']);
			}

			if (!empty($parameters['variant_type'])) {
				if (!$validationclass->validVariantType($parameters['variant_type'])) {
					$this->error("Invalid Variant Type");
					return [];
				}
				$find_ids_query .= "
				AND		r.variant_type = ?";
				$database->AddParam($parameters['variant_type']);
			}

            // Order Clause (applied in outer query against product_products as alias p)
            $order_by = '';
            switch ($controls['sort'] ?? '') {
				case 'name':
                    $order_by = " ORDER BY p.name";
					break;
				case 'date_added':
                    $order_by = " ORDER BY p.date_added";
					break;
				case 'code':
                    $order_by = " ORDER BY p.code";
					break;
				case 'type':
                    $order_by = " ORDER BY p.type";
					break;
				case 'status':
                    $order_by = " ORDER BY p.status";
					break;
				case 'category':
                    // We cannot safely order by r.parent_id without changing DISTINCT behavior; default to code
                    $order_by = " ORDER BY p.code";
					break;
				case 'id':
                    $order_by = " ORDER BY p.id";
					break;
				case 'price':
                    $order_by = " ORDER BY p.code";
					break;
				case 'quantity':
                    $order_by = " ORDER BY p.code";
					break;
				case 'sku':
                    $order_by = " ORDER BY p.code";
					break;
				default:
                    $order_by = " ORDER BY p.code";
			}

            // Build final query using outer select to allow ORDER BY on non-selected columns
            $final_query = "SELECT ids.id FROM (".$find_ids_query.") ids JOIN product_products p ON p.id = ids.id".$order_by;
            // Limit Clause
            $final_query .= $this->limitClause($parameters);

            $rs = $database->Execute($final_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

            $items = array();
            while ($row = $rs->FetchRow()) {
                // p.id is first selected; some drivers may return associative keys
                $id = isset($row['id']) ? $row['id'] : $row[0];
                $item = new Item($id);
				$this->incrementCount();
				array_push($items,$item);
			}
			return $items;
		}

		/**
		 * Search for messages based on a search string
		 *
		 * @param array $parameters Search parameters
		 * @return array|int Array of Content\Message objects or 0 on error
		 */
		public function searchAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Add search_tags searching
			if (!empty($parameters['search']) && !empty($advanced['categories'])) {
				$find_product_query = "
					SELECT DISTINCT(stx.object_id)
					FROM search_tags_xref stx
					INNER JOIN search_tags st ON stx.tag_id = st.id
					WHERE st.class = 'Product::Item'
					AND (
						st.category LIKE ?
						OR st.value LIKE ?
					)
				";
                $database->AddParams(['%'.$advanced['categories'].'%','%'.$parameters['search'].'%']);
			}
			elseif (!empty($parameters['search_tags'])) {
				// Join to the existing query
				$find_product_query = "
					SELECT DISTINCT(stx.object_id)
					FROM search_tags_xref stx
					INNER JOIN search_tags st ON stx.tag_id = st.id
					WHERE st.class = 'Product::Item'
					AND st.value LIKE ?
				";
                $database->AddParams(['%'.$parameters['search'].'%','%'.$parameters['search'].'%']);
			}
			else {
				$find_product_query = "
					SELECT DISTINCT(p.id)
					FROM product_products p
					LEFT OUTER JOIN
							product_relations r
					ON		p.id = r.product_id
					WHERE	p.status != 'DELETED'
				";
			}

			$rs = $database->Execute($find_product_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$items = array();
			while (list($id) = $rs->FetchRow()) {
				$item = new Item($id);
				$this->incrementCount();
				array_push($items,$item);
			}

			return $items;
		}
	}
