<?php
	namespace Event;
	# Event Logging and Querying

	require THIRD_PARTY.'/autoload.php';
	use Elasticsearch\ClientBuilder;

	class Item {
		public $error;
		private $index = "events";
		private $type = "event";
		private $_ES_CLIENT;

		public function __construct() {
		}

		public function add($type,$params) {
			# Connect to ElasticSearch
			$client = ClientBuilder::create()->setHosts($GLOBALS['_config']->elasticsearch->hosts)->build();
			$idxParams = array(
				"index"	=> $this->index,
				"type"	=> $type,
				"body"	=> $params
			);
			app_log("Event Add: ".print_r(json_encode($idxParams),true),'debug',__FILE__,__LINE__);
			$client->indexName = 'events';
			$response = $client->index($idxParams);
			return 1;
		}
	}
