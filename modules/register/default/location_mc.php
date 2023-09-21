<?php
    $site = new \Site();
    $page = $site->page();

    if ($_REQUEST['id']) {
        $location = new \Register\Location($_REQUEST['id']);
    }

    if (! $location->id) {
        $page->addError("Location not found");
        return 404;
    }

    if ($location->organization()->id != $GLOBALS['_SESSION_']->customer->organization()->id && $location->customer()->id != $GLOBALS['_SESSION_']->customer->id) {
        $page->addError("Location not found");
        return 404;
    }