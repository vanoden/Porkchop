<?
	namespace Product;

	class ItemList {
		public $count;
		public $error;

		public function find($parameters) {
			$this->error = '';

			$find_product_query = "
				SELECT	DISTINCT(p.id)
				FROM	product_products p
				LEFT OUTER JOIN
						product_relations r
				ON		r.product_id = p.id
				WHERE	p.status != 'DELETED'";

			# Filter on Given Parameters
			if (array_key_exists('type',$parameters))
				$find_product_query .= "
				AND		p.type = ".$GLOBALS['_database']->qstr($parameters["type"],get_magic_quotes_gpc());
			if (array_key_exists('status',$parameters))
				$find_product_query .= "
				AND		p.status = ".$GLOBALS['_database']->qstr($parameters["type"],get_magic_quotes_gpc());
			else
				$find_product_query .= "
				AND		p.status = 'ACTIVE'";

			if (array_key_exists('category_code',$parameters)) {
				if (is_array($parameters['category_code'])) {
					$category_ids = array();
					foreach ($parameters['category_code'] as $category_code)
					{
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

			if (array_key_exists("category",$parameters)) {
				# Get Parent ID
				$_parent = new Product($parameters["category"]);
				$category_id = $_parent->id;

				if (! $category_id)
				{
					$this->error = "Invalid Category";
					return null;
				}
				$find_product_query .= "
				AND		r.parent_id = $category_id";
			}
			elseif (array_key_exists('category_id',$parameters)) $find_product_query .= "
				AND		r.parent_id = $category_id";

			if (array_key_exists('status',$parameters) and preg_match('/^(active|hidden|deleted)$/i',$parameters["status"])) $find_product_query .= "
				AND		p.status = ".$parameters["status"];

			if (array_key_exists('id',$parameters) and preg_match('/^\d+$/',$parameters['id'])) $find_product_query .= "
				AND		p.id = ".$parameters['id'];

			if (isset($parameters['_sort']) && preg_match('/^[\w\_]+$/',$parameters['_sort']))
				$find_product_query .= "
				ORDER BY p.".$parameters['_sort'];
			else	
				$find_product_query .= "
				ORDER BY r.view_order,p.name";

			#app_log("Find Products Query: ".preg_replace("/(\n|\r)/","",$find_product_query),'debug',__FILE__,__LINE__);
			$rs = $GLOBALS['_database']->Execute($find_product_query);
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