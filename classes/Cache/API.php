<?php
	namespace Cache;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->admin_role('administrator');
			$this->_name = 'cache';
			$this->_version = '0.3.2';
			$this->_release = '2026-03-19';
			parent::__construct();
		}

		###################################################
		### Get List of Cache Keys						###
		###################################################
		public function findKeys() {
			$client = $GLOBALS['_CACHE_'];
	
			if (! $GLOBALS['_SESSION_']->customer->can('manage cache')) $this->deny();
	
			$object = null;
			if (isset($_REQUEST['object']) && preg_match('/^\w[\w\-\.\_]*$/',$_REQUEST['object']) > 0) $object = $_REQUEST['object'];
			$keyArray = array();
			$keys = $client->keys($object);
	
			$response = new \APIResponse();
			$response->addElement('key', $keys);
	
			$response->print();
		}

		###################################################
		### Get List of Cache Key Names					###
		###################################################
		public function keyNames() {
			$client = $GLOBALS['_CACHE_'];
	
			if (! $GLOBALS['_SESSION_']->customer->can('manage cache')) $this->deny();
	
			$keys = $client->keyNames();
	
			$response = new \APIResponse();
			$response->addElement('keyName', $keys);
	
			$response->print();
		}
	
		###################################################
	
		###################################################
		### Get Specific Item from Cache				###
		###################################################
		public function getItem() {
			if (! $GLOBALS['_SESSION_']->customer->can('manage cache')) $this->deny();
			$cache_key = $_REQUEST['object']."[".$_REQUEST['id']."]";
			$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			if ($cache->error()) {
				app_log("Error in cache mechanism: ".$cache->error(),'error',__FILE__,__LINE__);
			}
	
			$object = $cache->get();
	
			$response = new \APIResponse();
			$response->addElement('object', $object);
	
			$response->print();
		}
	
		###################################################
		### Delete Specific Item from Cache				###
		###################################################
		public function deleteItem() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");
			if (! $GLOBALS['_SESSION_']->customer->can('manage cache')) $this->deny();
			$cache_key = $_REQUEST['object']."[".$_REQUEST['id']."]";
			$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			if ($cache->error()) {
				app_log("Error in cache mechanism: ".$cache->error(),'error',__FILE__,__LINE__);
			}
	
			$count = 0;
			if ($cache->exists()) {
				$cache->delete();
				$count = 1;
			}
	
			$response = new \APIResponse();
			$response->addElement('success', 1);
			$response->addElement('count', $count);
	
			$response->print();
		}
	
		###################################################
		### Cache Stats									###
		###################################################
		public function stats() {
			$client = $GLOBALS['_CACHE_'];
	
			if (! $GLOBALS['_SESSION_']->customer->can('manage cache')) $this->deny();
	
			$response = new \APIResponse();
			$response->addElement('success', 1);
			$response->addElement('stats', $client->stats());
	
			$response->print();
		}
	
		###################################################
		### Flush Cache									###
		###################################################
		public function flushCache() {
			$client = $GLOBALS['_CACHE_'];
	
			if (! $GLOBALS['_SESSION_']->customer->can('manage cache')) $this->deny();
	
			$client->flush();
	
			$response = new \APIResponse();
			$response->addElement('success', 1);
	
			$response->print();
		}

		public function _methods() {
			return array(
				'findKeys'	=> array(
					'object'	=> array(),
				),
				'keyNames'	=> array(),
				'getItem'	=> array(
					'object'	=> array('required' => true),
					'id'		=> array('required' => true),
				),
				'deleteItem'	=> array(
					'object'	=> array('required' => true),
					'id'		=> array('required' => true),
				),
				'stats'	=> array(
				),
				'flushCache'	=> array(
				),
			);
		}
	}
