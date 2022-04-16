<?php
	namespace Cache;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->admin_role('administrator');
			$this->_name = 'cache';
			$this->_version = '0.2.1';
			$this->_release = '2020-06-10';
			parent::__construct();
		}

		###################################################
		### Get List of Cache Keys						###
		###################################################
		public function findKeys() {
			$client = $GLOBALS['_CACHE_'];
	
			if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) $this->error('Permission denied');
	
			$object = null;
			if (isset($_REQUEST['object']) && preg_match('/^\w[\w\-\.\_]*$/',$_REQUEST['object']) > 0) $object = $_REQUEST['object'];
			$keyArray = array();
			$keys = $client->keys($object);
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->key = $keys;
	
			print $this->formatOutput($response);
		}

		###################################################
		### Get List of Cache Key Names					###
		###################################################
		public function keyNames() {
			$client = $GLOBALS['_CACHE_'];
	
			if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) $this->error('Permission denied');
	
			$keys = $client->keyNames();
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->keyName = $keys;
	
			print $this->formatOutput($response);
		}
	
		###################################################
	
		###################################################
		### Get Specific Item from Cache				###
		###################################################
		public function getItem() {
			if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) $this->error('Permission denied');
			$cache_key = $_REQUEST['object']."[".$_REQUEST['id']."]";
			$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			if ($cache->error) {
				app_log("Error in cache mechanism: ".$cache->error,'error',__FILE__,__LINE__);
			}
	
			$object = $cache->get();
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->object = $object;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Delete Specific Item from Cache				###
		###################################################
		public function deleteItem() {
			if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) $this->error('Permission denied');
			$cache_key = $_REQUEST['object']."[".$_REQUEST['id']."]";
			$cache = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			if ($cache->error) {
				app_log("Error in cache mechanism: ".$cache->error,'error',__FILE__,__LINE__);
			}
	
			$count = 0;
			if ($cache->exists()) {
				$cache->delete();
				$count = 1;
			}
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->count = $count;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Cache Stats									###
		###################################################
		public function stats() {
			$client = $GLOBALS['_CACHE_'];
	
			if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) $this->error('Permission denied');
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->stats = $client->stats();
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Flush Cache									###
		###################################################
		public function flushCache() {
			$client = $GLOBALS['_CACHE_'];
	
			if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) $this->error('Permission denied');
	
			$client->flush();
	
			$response = new \HTTP\Response();
			$response->success = 1;
	
			print $this->formatOutput($response);
		}

		public function _methods() {
			return array(
				'ping'	=> array(),
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
