<?php
    require_module("event");
    $event_item = new EventItem();
    $event_item->search(
        "ActionRequest",
        array("code" => "56532596d3857")
    );
