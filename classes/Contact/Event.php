<?

	namespace Contact;

	class Event {
		public $error;

		public function __construct() {
			app_log("Initializing Contact Module",'debug',__FILE__,__LINE__);

			$_init = new Schema();
			if ($_init->error)
			{
				$this->error = "Error initializing Contact module: ".$_init->error;
				return null;
			}
		}
		public function add($parameters) {
			$add_object_query = "
				INSERT
				INTO	contact_events
				(		id,date_event,content,status)
				VALUES
				(		null,sysdate(),?,'NEW')
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(json_encode($parameters))
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Contact::Event::add(): ".$GLOBALS['_database']->ErrorMsg();
				return NULL;
			}

			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}
		public function update($parameters) {
			$update_object_query = "
				UPDATE	contact_events
				SET		id = id";
			if (in_array($parameters["status"],array("NEW","OPEN","CLOSED"))) {
				$update_object_query .= ",
						status = '".$parameters["status"]."'";
			}
			$update_object_query .= "
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Contact::Event::update(): ".$GLOBALS['_database']->ErrorMsg();
				return NULL;
			}
			return $this->details();
		}

		public function find($parameters = array()) {
			$find_object_query = "
				SELECT	id
				FROM	contact_events
				WHERE	id = id
			";
			if (preg_match('/^\w+$/',$parameters['status']))
				$find_object_query = "
				AND		status = '".$parameters['status']."'";
			$find_object_query .= "
				ORDER BY date_event";
			$rs = $GLOBALS['_database']->Execute($find_object_query);
			if (! $rs) {
				$this->error = "SQL Error in Contact::Event::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = $this->details($id);
				array_push($objects,$object);
			}
			return $objects;
		}
		public function details() {
			$get_object_query = "
				SELECT	id,
						date_event,
						status,
						content
				FROM	contact_events
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in Contact::Event::details(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$object = $rs->FetchNextObject(false);
			$content = json_decode($object->content);
			$object->content = $content;
			return $object;
		}
	}
?>
