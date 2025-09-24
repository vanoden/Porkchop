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

    $locationOrg = $location->organization();
    $customerOrg = $GLOBALS['_SESSION_']->customer->organization();
    if ((!$locationOrg || !$customerOrg || $locationOrg->id != $customerOrg->id) && $location->customer()->id != $GLOBALS['_SESSION_']->customer->id) {
        $page->addError("Location not found");
        return 404;
    }