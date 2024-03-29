<?php
	$page = new \Site\Page();
	$page->requireAuth();
    $page->requireOrganization();

	if (empty($GLOBALS['_SESSION_']->customer->organization()->id)) $page->addError("Your registration has not been completed.  Please make sure you've validated your email and contact ".$GLOBALS['_config']->site->support_email.' for assistance.');

    // get current messages for user
    $params = array(
        "recipient_id"   => $GLOBALS['_SESSION_']->customer->id,
        "acknowledged" => false
    );
    $params['acknowledged'] = 'unread';
    if (isset($_REQUEST['filter']) && isset($_REQUEST['seeAcknowledged'])) $params['acknowledged'] = 'read';

    $siteMessage = new \Site\SiteMessagesList();
    $siteMessageDelivery = new \Site\SiteMessageDelivery();
    $siteMessages = $siteMessage->find($params);
    if ($siteMessage->error()) $page->addError($siteMessage->error());