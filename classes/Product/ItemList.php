<?php
	namespace Product;

	class ItemList {
		public $count;
		public $error;

		public function find($parameters = array()) {
			$this->error = '';

			$find_product_query = "
				SELECT	DISTINCT(p.id)
				FROM	product_products p
				LEFT OUTER JOIN
						product_relations r
				ON		r.product_id = p.id
				WHERE	p.status != 'DELETED'";

			$bind_params = array();
			# Filter on Given Parameters
			if (isset($parameters['type'])) {
				if (is_array($parameters['type'])) {
					$find_product_query .= "
					AND		p.type in (";
					$count = 0;
					foreach ($parameters['type'] as $type) {
						if ($count) $find_product_query .= ",";
						$count ++;
						$find_product_query .= $GLOBALS['_database']->qstr($type,get_magic_quotes_gpc());
					}
					$find_product_query .= ")";
				}
				else {
					$find_product_query .= "
					AND		p.type = ?";
					array_push($bind_params,$parameters["type"]);
				}
			}
			if (isset($parameters['status'])) {
				$find_product_query .= "
				AND		p.status = ?";
				array_push($bind_params,strtoupper($parameters["status"]));
			}
			else
				$find_product_query .= "
				AND		p.status = 'ACTIVE'";

			if (isset($parameters['category_code'])) {
				if (is_array($parameters['category_code'])) {
					$category_ids = array();
					foreach ($parameters['category_code'] as $category_code) {
						list($category) = $this->find(
							array(
								code	=> $category_code
							)
						);
						array_push($category_ids,$category->id);
					}
					$find_product_query .= "
					AND	r.parent_id in (".join(',',$category_ids).")";
				}
				elseif(preg_match('/^[\w\-\_\.\s]+$/',$parameters['category_code'])) {
					list($category) = $this->find(
						array(
							code	=> $parameters['category_code']
						)
					);
					$parameters['category_id'] = $category->id;
				}
			}

			if (isset($parameters['category'])) {
				# Get Parent ID
				$_parent = new Product($parameters["category"]);
				$category_id = $_parent->id;

				if (! $category_id)
				{
					$this->error = "Invalid Category";
					return null;
				}
				$find_product_query .= "
				AND		r.parent_id = ?";
				array_push($bind_params,$category_id);
			}
			elseif (isset($parameters['category_id'])) {
				$find_product_query .= "
				AND		r.parent_id = ?";
				array_push($bind_params,$category_id);
			}
			if (isset($parameters['id']) and preg_match('/^\d+$/',$parameters['id'])) {
				$find_product_query .= "
				AND		p.id = ?";
				array_push($bind_params,$parameters['id']);
			}
			if (isset($parameters['_sort']) && preg_match('/^[\w\_]+$/',$parameters['_sort']))
				$find_product_query .= "
				ORDER BY p.".$parameters['_sort'];
			else	
			    $find_product_query .= "ORDER BY p.id";

			query_log($find_product_query);
			$rs = $GLOBALS['_database']->Execute($find_product_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$items = array();
			while (list($id) = $rs->FetchRow()) {
				$item = new Item($id);
				array_push($items,$item);
			}
			return $items;
		}
	}
?>
