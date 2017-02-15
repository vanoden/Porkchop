<?
    require_module("action");
    
    $_requests = new ActionRequests();
    $requests = $_requests->find(
        array(
            "status"    => array("NEW","ASSIGNED","OPEN")
        )
    );
?>