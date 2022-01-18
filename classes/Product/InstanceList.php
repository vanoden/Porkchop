<?php
	namespace Product;

	class InstanceList {
		public $error;
		public $count = 0;

		# Return a list of hubs
		public function find($parameters = '',$recursive = true) {
			$this->error = null;
			$find_objects_query = "
				SELECT	ma.asset_id
				FROM	monitor_assets AS ma
				JOIN	product_products AS pi
				ON		ma.product_id = pi.id
				LEFT OUTER JOIN
						register_organizations AS ro
				ON		ma.organization_id = ro.id
				WHERE	pi.id = pi.id
			";
			$bind_params = array();
			if ($GLOBALS['_SESSION_']->customer->has_role('monitor admin')) {
				if (isset($parameters['organization_id']) && is_numeric($parameters['organization_id'])) {
					$find_objects_query .= "
					AND	ma.organization_id = ?";
					array_push($bind_params,$parameters['organization_id']);
				}
			}
			elseif (is_numeric($GLOBALS['_SESSION_']->customer->organization->id)) {
				$find_objects_query .= "
					AND	ma.organization_id = ?";
				array_push($bind_params,$GLOBALS['_SESSION_']->customer->organization->id);
			}
			else {
				$this->error = "Customer must belong to an organization";
				return null;
			}

			if (isset($parameters['id']) && preg_match('/^\d+$/',$parameters['id'])) {
				$find_objects_query .= "
				AND		asset_id = ?";
				array_push($bind_parms,$parameters['id']);
			}
			if (isset($parameters['code']) && preg_match('/^[\w\-\.\_\s]+$/',$parameters['code'])) {
				$find_objects_query .= "
				AND		asset_code = ?";
				array_push($bind_params,$parameters['code']);
				app_log("Getting instances with code '".$parameters['code']."'",'debug',__FILE__,__LINE__);
			}
			if (isset($parameters['product_id']) && preg_match('/^\d+$/',$parameters['product_id'])) {
				$find_objects_query .= "
				AND		pi.id = ?";
				array_push($bind_params,$parameters['product_id']);
			}
			if (isset($parameters['product_code'])) {
				$find_objects_query .= "
				AND		pi.code = ?";
				array_push($bind_params,$parameters['product_code']);
			}

            if (!is_array($parameters)) {
                $parameters = array();
                $parameters['_sort_order'] = 'ASC';
            }
			if (isset($parameters['_sort_order']) && $parameters['_sort_order'] != 'DESC') $parameters['_sort_order'] = 'ASC';
			if (array_key_exists("_sort",$parameters)) {
				if($parameters['_sort'] == 'organization') {
					$find_objects_query .= "
					ORDER BY ro.name ".$parameters['_sort_order']."
					";
				}
				elseif($parameters['_sort'] == 'product') {
					$find_objects_query .= "
					ORDER BY pi.code ".$parameters['_sort_order']."
					";
				}
				else
					$find_objects_query .= "
					ORDER BY asset_code ".$parameters['_sort_order']."
					";
			}
			else {
				$find_objects_query .= "
				ORDER BY asset_code ASC";
			}

			if (isset($parameters['_limit']) and preg_match('/^\d+$/',$parameters['_limit'])) {
				if (preg_match('/^\d+$/',$parameters['_offset']))
					$find_objects_query .= "
					LIMIT	".$parameters['_offset'].",".$parameters['_limit'];
				else
					$find_objects_query .= "
					LIMIT	".$parameters['_limit'];
			}

			query_log($find_objects_query);
			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Product::InstantList::find:" .$GLOBALS['_database']->ErrorMsg();
				app_log(preg_replace("/(\n|\r)/","",preg_replace("/\t/"," ",$find_objects_query)),'debug',__FILE__,__LINE__);
				return null;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				if ($recursive) {
					app_log("Adding instance $id to InstanceList",'trace',__FILE__,__LINE__);
					if (isset($parameters['_flat']) && $parameters['_flat']) {
						$object = new Instance($id,$parameters['_flat']);
					}
					else {
						$object = new Instance($id);
					}
					if ($object->error) {
						$this->error = "Error loading asset: ".$object->error;
						return null;
					}
					array_push($objects,$object);
				}
				else {
					array_push($objects,$id);
				}
				$this->count ++;
			}
			return $objects;
		}
	}
