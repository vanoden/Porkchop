<?
	# Event Logging and Querying

	require THIRD_PARTY.'/vendor/autoload.php';
	use Elasticsearch\ClientBuilder;

	class EventItem {
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

		public function search($type,$params,$sort = []) {
			# Connect to ElasticSearch
			$client = ClientBuilder::create()->setHosts($GLOBALS['_config']->elasticsearch->hosts)->build();
			$idxParams = [
				"index"	=> $this->index,
				"type"	=> $type,
				"body"	=> array(
					"query"	=> array(
						"bool" => array(
							"must" => array()
						)
					)
				)
			];
			$fields = array();
			while (list($key,$value) = each($params)) {
				array_push($fields,array("match" => array($key => $value)));
			}
			$idxParams["body"]["query"]["bool"]["must"] = $fields;
			app_log("Event Search: ".print_r(json_encode($idxParams),true),'debug',__FILE__,__LINE__);
			$response = $client->search($idxParams);
			return $response;
		}
	}

?>
