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
		
		public function search($parameters = []) {
			if (!isset($parameters['search_tags'])) $parameters['search_tags'] = true;
			return $this->find($parameters);
		}

		public function find($parameters = [],$controls = []) {

			$this->clearError();
            $this->resetCount();

            // For Validation
            $validationclass = new \Product\Item();

			$find_product_query = "
				SELECT	DISTINCT(p.id)
				FROM	product_products p
				LEFT OUTER JOIN
						product_relations r
				ON		p.id = r.product_id
				WHERE	p.status != 'DELETED'";

			$bind_params = array();

            if (!empty($parameters['search'])) {
                if (!$validationclass->validSearch($parameters['search']) ) {
                    $this->error("Invalid Search String");
                    return null;
                }
                $find_product_query .= "
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
                array_push($bind_params,$search_string,$search_string,$search_string);
            }
			# Filter on Given Parameters
			if (isset($parameters['type'])) {
				if (is_array($parameters['type'])) {
					$find_product_query .= "
					AND		p.type in (";
					$count = 0;
					foreach ($parameters['type'] as $type) {
                        if (!$validationclass->validType($type)) {
                            $this->error("Invalid Type: ".$type);
                            return null;
                        }
						if ($count) $find_product_query .= ",";
						$count ++;
						$find_product_query .= $GLOBALS['_database']->qstr($type,get_magic_quotes_gpc());
					}
					$find_product_query .= ")";
				} else {
                    if (!$validationclass->validType($parameters["type"])) {
                        $this->error("Invalid Type: ".$parameters["type"]);
                        return null;
                    }
					$find_product_query .= "
					AND		p.type = ?";
					array_push($bind_params,$parameters["type"]);
				}
			}
			if (isset($parameters['status']) && is_array($parameters['status'])) {
                foreach ($parameters['status'] as $status) {
                    if (! $validationclass->validStatus($status)) {
                        $this->error("Invalid Status: ".$status);
                        return null;
                    }
                    $find_product_query .= "
                    AND     p.status IN ('".implode("','",$parameters['status'])."')";
                }
            } elseif (!empty($parameters['status']) && $validationclass->validClass($parameters['status'])) {
				$find_product_query .= "
				AND		p.status = ?";
				array_push($bind_params,strtoupper($parameters["status"]));
			} else
				$find_product_query .= "
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
					$find_product_query .= "
					AND	r.parent_id in (".join(',',$category_ids).")";
				} elseif(preg_match('/^[\w\-\_\.\s]+$/',$parameters['category_code'])) {
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
					return null;
				}
				$find_product_query .= "
				AND		r.parent_id = ?";
				array_push($bind_params,$category_id);

			} elseif (isset($parameters['category_id'])) {
				$find_product_query .= "
				AND		r.parent_id = ?";
				array_push($bind_params,$parameters['category_id']);
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
			    $find_product_query .= "
				ORDER BY p.id";

            if (isset($controls['limit']) && is_numeric($controls['limit'])) {
                $find_product_query .= "
                LIMIT   ".$controls['limit'];
                if (isset($controls['offset']) && is_numeric($controls['offset'])) {
                    $find_product_query .= "
                    OFFSET  ".$controls['offset'];
                }
            }

			query_log($find_product_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($find_product_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$items = array();
			while (list($id) = $rs->FetchRow()) {
				$item = new Item($id);
                $this->incrementCount();
				array_push($items,$item);
			}

            // Add search_tags searching
            if (isset($parameters['search_tags']) && !empty($parameters['search_tags'])) {
				
				$bind_params = array();
				
				// Join to the existing query
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
                array_push($bind_params, '%'.$parameters['search'].'%','%'.$parameters['search'].'%');
            }

			query_log($find_product_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($find_product_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			while (list($id) = $rs->FetchRow()) {
				$item = new Item($id);
                $this->incrementCount();
				array_push($items,$item);
			}

			return $items;
		}
	}
