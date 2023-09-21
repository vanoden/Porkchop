<?php
	namespace Navigation;

	class API Extends \API {

		public function __construct() {
			$this->_name = 'navigation';
			$this->_version = '0.2.1';
			$this->_release = '2022-11-10';
			$this->_schema = new Schema();
			$this->_admin_role = 'navigation manager';
			parent::__construct();
		}

		###################################################
		### Query Menu List								###
		###################################################
		function findMenus() {
			# Default StyleSheet
			if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'navigation.menu.xsl';

			# Initiate Page List
			$menu_list = new \Navigation\MenuList();

			# Find Matching Threads
			$parameters = array();
			$menus = $menu_list->find($parameters);

			# Error Handling
			if ($menu_list->error) $this->error($menu_list->error);
			else{
				$this->response->menu = $menus;
				$this->response->count = $menu_list->count();
				$this->response->success = 1;
			}

			# Send Response
			api_log($this->response);
			print $this->formatOutput($this->response);
		}

		###################################################
		### Get Menu									###
		###################################################
		function getMenu() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'navigation.menu.xsl';

			$parameters = array();

			if (!empty($_REQUEST['code'])) {
				$menu = new \Navigation\Menu();
				if ($menu->get($_REQUEST['code'])) {
					$this->response->request = $_REQUEST;
					$this->response->menu = $menu;
					$this->response->success = 1;
				}
				elseif ($menu->error()) {
					$this->error($menu->error());
				}
				else {
					$this->error("Menu not found");
				}
			}
			else $this->error("menu code required");

			# Send Response
			api_log($this->response);
			print $this->formatOutput($this->response);
		}

		###################################################
		### Add Menu									###
		###################################################
		function addMenu() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'navigation.menu.xsl';

			$parameters = array();

			$menu = new \Navigation\Menu();

			if (! isset($_REQUEST['code'])) $this->error("code required");
			if (! $menu->validCode($_REQUEST['code'])) $this->error("Invalid Code");
			if (! $menu->validTitle($_REQUEST['title'])) $this->error("Invalid Title");

			$parameters['code'] = $_REQUEST['code'];
			$parameters['title'] = $_REQUEST['title'];
			if ($menu->add($parameters)) {
				$response->menu = $menu;
				$response->success = 1;
			}
			else {
				$this->error($menu->error());
			}

			# Send Response
			api_log($this->response);
			print $this->formatOutput($this->response);
		}

		###################################################
		### Get Menu Items								###
		###################################################
		function findItems() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'navigation.item.xsl';

			$parameters = array();
			$itemlist = new \Navigation\ItemList();

			if (!empty($_REQUEST['menu_code'])) {
				$menu = new \Navigation\Menu();
				if ($menu->get($_REQUEST['menu_code'])) {
					$parameters['menu_id'] = $menu->id;
					if (isset($_REQUEST['parent_id'])) {
						$parameters['parent_id'] = $_REQUEST['parent_id'];
					}
				}
				else {
					$this->error("Menu '".$_REQUEST['menu_code']."' not found");
				}
			}
			if (!empty($_REQUEST['target'])) {
				$item = new \Navigation\Item();
				if ($item->validTarget($_REQUEST['target']))
					$parameters['target'] = $_REQUEST['target'];
				else $this->error("Invalid target");
			}

			$items = $itemlist->find($parameters);
			if ($itemlist->error()) $this->error($itemlist->error());
			else {
				$this->response->item = $items;
				$this->response->count = $itemlist->count();
				$this->response->success = 1;
			}

			# Send Response
			api_log($this->response);
			print $this->formatOutput($this->response);
		}

		###################################################
		### Add Menu Item								###
		###################################################
		function addItem() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'navigation.item.xsl';

			$parameters = array();

			if (! isset($_REQUEST['menu_code'])) $this->error("menu_code required");
			$menu = new \Navigation\Menu();
			if (! $menu->get($_REQUEST['menu_code'])) $this->error("Menu not found");

			$parameters['menu_id'] = $menu->id;	
			$parameters['title'] = $_REQUEST['title'];
			$parameters['target'] = $_REQUEST['target'];
			$parameters['alt'] = $_REQUEST['alt'];
			$parameters['description'] = $_REQUEST['description'];
			$parameters['view_order'] = $_REQUEST['view_order'];

			$item = new \Navigation\Item();
			if ($item->add($parameters)) {
				$response->item = $item;
				$response->success = 1;
			}
			elseif ($item->error()) {
				$this->error($item->error());
			}

			# Send Response
			api_log($this->response);
			print $this->formatOutput($this->response);
		}

		###################################################
		### Update Menu Item							###
		###################################################
		function updateItem() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'navigation.item.xsl';

			$parameters = array();

			if (!empty($_REQUEST['title']) && !empty($_REQUEST['menu_code'])) {
				$menu = new \Navigation\Menu();
				$item = $menu->item($_REQUEST['title']);
				if ($item->exists()) $_REQUEST['id'] = $item->id;
				else $this->error("Item not found");
			}
			if (! isset($_REQUEST['id'])) $this->error("id required");
			$item = new \Navigation\Item($_REQUEST['id']);
			if ($item->error) $this->error($item->error);

			if (! $item->id) $this->error("Item not found");

			$parameters['title'] = $_REQUEST['title'];
			$parameters['target'] = $_REQUEST['target'];
			$parameters['alt'] = $_REQUEST['alt'];
			$parameters['description'] = $_REQUEST['description'];
			$parameters['view_order'] = $_REQUEST['view_order'];

			if ($item->update($parameters)) {
				$this->response->request = $_REQUEST;
				$this->response->item = $item;
				$this->response->success = 1;
			}
			elseif ($item->error()) {
				$this->error($item->error());
			}

			# Send Response
			api_log($this->response);
			print $this->formatOutput($this->response);
		}

		###################################################
		### Dump Menu Data for Javascript				###
		###################################################
		function menuObject() {
			$response = new \HTTP\Response();

			if (empty($_REQUEST['code'])) $this->error("menu code required");

			$menu = new \Navigation\Menu();
			if (! $menu->get($_REQUEST['code'])) $this->error("menu not found");

			$this->response->item = $menu->cascade();
			$this->response->success = 1;

			# Send Response
			api_log($this->response);
			print $this->formatOutput($this->response);
		}

		public function _methods() {
			$menuList = new \Navigation\MenuList();
			$menus = $menuList->find();

			return array(
				'ping'		=> array(),
				'findMenus'	=> array(
				),
				'getMenu'	=> array(
					'code'		=> array('required' => true),
					'options'	=> array_keys($menus)
				),
				'addMenu'	=> array(
					'code'		=> array('required'	=> true),
					'title'		=> array('required'	=> true),
				),
				'findItems'	=> array(
					'menu_code'		=> array(),
					'parent_id'		=> array(),
					'target'		=> array(),
				),
				'addItem'	=> array(
					'menu_code'		=> array('required' => true),
					'title'			=> array('required' => true),
					'target'		=> array('required' => true),
					'alt'			=> array(),
					'description'	=> array(),
					'view_order'	=> array(),
				),
				'updateItem'	=> array(
					'menu_code'		=> array('required' => true),
					'code'			=> array('required' => true),
					'title'			=> array(),
					'target'		=> array(),
					'alt'			=> array(),
					'description'	=> array(),
					'view_order'	=> array(),
				),
				'menuObject'	=> array(
					'code'		=> array('required' => true),
				)
			);
		}
	}