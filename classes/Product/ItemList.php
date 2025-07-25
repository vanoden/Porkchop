<?php
	namespace Product;

	class ItemList Extends \BaseListClass {

		public function __construct() {
			$this->_modelName = '\Product\Item';
		}

		public function count($parameters = []) {
			if (!empty($this->_count)) return $this->_count;
			$this->_count = count($this->find($parameters));
			return $this->_count;
		}

		public function getAllProducts($type = 'unique') {
			$query = "
				SELECT id
				FROM product_products
				WHERE status = 'ACTIVE'
			";
			if ($type !== null) $query .= " AND type = ?";
			$query .= " ORDER BY id";
			$bind_params = $type !== null ? [$type] : [];

			query_log($query, $bind_params);
			$rs = $GLOBALS['_database']->Execute($query, $bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$productIds = array();
			while (list($id) = $rs->FetchRow()) {
				$productIds[] = $id;
			}

			return $productIds;
		}

		public function findAdvanced($parameters = [], $controls = [], $advanced = []): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();
	
			// For Validation
			$validationclass = new \Product\Item();

			$find_objects_query = "
				SELECT	DISTINCT(p.id)
				FROM	product_products p
				LEFT OUTER JOIN
						product_relations r
				ON		p.id = r.product_id
				WHERE	p.status != 'DELETED'";

			if (!empty($parameters['search'])) {
				if (!$validationclass->validSearch($parameters['search']) ) {
					$this->error("Invalid Search String");
					return [];
				}
				$find_objects_query .= "
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
				$database->AddParams($search_string,$search_string,$search_string);
			}
			# Filter on Given Parameters
			if (isset($parameters['type'])) {
				if (is_array($parameters['type'])) {
					$find_objects_query .= "
					AND		p.type in (";
					$count = 0;
					foreach ($parameters['type'] as $type) {
						if (!$validationclass->validType($type)) {
							$this->error("Invalid Type: ".$type);
							return [];
						}
						if ($count) $find_objects_query .= ",";
						$count ++;
						$find_objects_query .= "'".$type."'";
					}
					$find_objects_query .= ")";
				}
				else {
					if (!$validationclass->validType($parameters["type"])) {
						$this->error("Invalid Type: ".$parameters["type"]);
						return [];
					}
					$find_objects_query .= "
					AND		p.type = ?";
					$database->AddParam($parameters["type"]);
				}
			}
			if (isset($parameters['status']) && is_array($parameters['status'])) {
				foreach ($parameters['status'] as $status) {
					if (! $validationclass->validStatus($status)) {
						$this->error("Invalid Status: ".$status);
						return [];
					}
					$find_objects_query .= "
					AND     p.status IN ('".implode("','",$parameters['status'])."')";
				}
			}
			elseif (!empty($parameters['status']) && $validationclass->validClass($parameters['status'])) {
				$find_objects_query .= "
				AND		p.status = ?";
				$database->AddParam(strtoupper($parameters["status"]));
			}
			else
				$find_objects_query .= "
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
					$find_objects_query .= "
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
				$find_objects_query .= "
				AND		r.parent_id = ?";
				$database->AddParam($category_id);

			} elseif (isset($parameters['category_id'])) {
				$find_objects_query .= "
				AND		r.parent_id = ?";
				$database->AddParam($parameters['category_id']);
			}

			if (isset($parameters['id']) and preg_match('/^\d+$/',$parameters['id'])) {
				$find_objects_query .= "
				AND		p.id = ?";
				$database->AddParam($parameters['id']);
			}

			// Order Clause
			switch ($controls['sort'] ?? '') {
				case 'name':
					$find_objects_query .= "
						ORDER BY p.name";
					break;
				case 'date_added':
					$find_objects_query .= "
						ORDER BY p.date_added";
					break;
				case 'code':
					$find_objects_query .= "
						ORDER BY p.code";
					break;
				case 'type':
					$find_objects_query .= "
						ORDER BY p.type";
					break;
				case 'status':
					$find_objects_query .= "
						ORDER BY p.status";
					break;
				case 'category':
					$find_objects_query .= "
						ORDER BY r.parent_id";
					break;
				case 'id':
					$find_objects_query .= "
						ORDER BY p.id";
					break;
				case 'price':
					$find_objects_query .= "
						ORDER BY p.price";
					break;
				case 'quantity':
					$find_objects_query .= "
						ORDER BY p.quantity";
					break;
				case 'sku':
					$find_objects_query .= "
						ORDER BY p.sku";
					break;
				default:
					$find_objects_query .= "
						ORDER BY p.code";
			}

			// Limit Clause
			$find_objects_query .= $this->limitClause($parameters);

			$rs = $database->Execute($find_objects_query);
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
				$database->AddParams('%'.$advanced['categories'].'%','%'.$parameters['search'].'%');
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
				$database->AddParams('%'.$parameters['search'].'%','%'.$parameters['search'].'%');
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

			while (list($id) = $rs->FetchRow()) {
				$item = new Item($id);
				$this->incrementCount();
				array_push($items,$item);
			}

			return $items;
		}
	}
