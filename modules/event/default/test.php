<?
    require_module("event");
    
    $event_item = new EventItem();
    $event_item->add(
		"test",
        array(
            "name"  => $_REQUEST['name'],
            "weight"    => $_REQUEST['weight'],
            "timestamp" => date("c"),
            "job"   => $_REQUEST['job']
        )
    );
	if ($event_item->error) {
		print "Error: ".$event_item->error;
	}
	else {
		print "Ok";
	}
	exit;
?>
