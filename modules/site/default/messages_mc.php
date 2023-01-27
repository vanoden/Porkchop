<?php
	$page = new \Site\Page();
	$page->requireAuth();

	if (empty($GLOBALS['_SESSION_']->customer->organization()->id)) $page->addError("Your registration has not been completed.  Please make sure you've validated your email and contact ".$GLOBALS['_config']->site->support_email.' for assistance.');

    // get current messages for user
    $params = array(
        "user_id"   => $GLOBALS['_SESSION_']->customer->id,
        "acknowledged" => false
    );

    if ($_REQUEST['btn_filter']) {
        if ($_REQUEST['seeAcknowledged']) $params['acknowledged'] = true;
    }

	$siteMessageDeliveryList = new \Site\SiteMessageDeliveryList();
	$siteMessageDeliveries = $siteMessageDeliveryList->find($params);
	if ($siteMessageDeliveryList->error()) $page->addError($siteMessageDeliveryList->error());