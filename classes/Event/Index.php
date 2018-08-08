<?
	namespace Event;
	# Event Logging and Querying

	require THIRD_PARTY.'/autoload.php';
	use Elasticsearch\ClientBuilder;

	class Index {
		public $error;
		private $index = "events";
		private $type;
		private $_ES_CLIENT;

		public function __construct($type) {
			$this->type = $type;
		}
		public function search($params,$sort = []) {
			# Connect to ElasticSearch
			$client = ClientBuilder::create()->setHosts($GLOBALS['_config']->elasticsearch->hosts)->build();
			$idxParams = [
				"index"	=> $this->index,
				"type"	=> $this->type,
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
