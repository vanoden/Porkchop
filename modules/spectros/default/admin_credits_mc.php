<?php
	if (! role('monitor admin'))
	{
		$GLOBALS['_page']->error = "Must have role 'monitor admin' to access this page";
		return;
	}
	require_once(MODULES."/monitor/_classes/default.php");
	require_once(MODULES."/spectros/_classes/default.php");

	if ($_REQUEST['organization_id'])
	{
		$_organization = new RegisterOrganization();
		$cur_org = $_organization->details($_REQUEST['organization_id']);

		$_credit = new CalibrationVerificationCredit();
		if ($_REQUEST['btn_submit'])
		{
			if ((preg_match('/^\d+$/',$_REQUEST['add_credits'])) and ($_REQUEST['add_credits'] > 0))
			{
				$_credit->add($_REQUEST['organization_id'],$_REQUEST['add_credits']);
				if ($_credir->error)
				{
					$GLOBALS['_page']->error = "Error adding credits: ".$_credit->error;
				}
				else
				{
					$GLOBALS['_page']->success = $_REQUEST['add_credits']." added successfully";
				}
			}
		}
		$credit_info = $_credit->get($_REQUEST['organization_id']);
		$credits = $credit_info->quantity;
	}
	else $credits = 0;

	# Get Organizations
	$_organization = new RegisterOrganization();
	$organizations = $_organization->find();
?>
