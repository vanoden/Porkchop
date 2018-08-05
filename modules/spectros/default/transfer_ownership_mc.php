<?php
	$page = new \Site\Page();

	if (isset($_REQUEST['btn_submit'])) {
		$asset = new \Monitor\Asset($_REQUEST['asset_id']);
		$old_org = $asset->organization;

		if (! $asset->id) {
			$page->error = "Asset not found";
		}
		else {
			$organization = new \Register\Organization($_REQUEST['organization_id']);
			if (! $organization->id) {
				$page->error = "Organization not found";
			}
			else {
				$asset->update(array('organization_id' => $_REQUEST['organization_id']));
				if ($asset->error) {
					$page->error = "Error transferring device: ".$asset->error;
				}
				elseif ($_REQUEST['transferDeviceAccountConfirm'] == 1) {
					$customer = new \Register\Customer();
					$customer->get($asset->code);
					if (! $customer->id) {
						$page->error = "Customer not found";
					}
					else {
						$customer->update(array("organization_id" => $_REQUEST['organization_id']));
						if ($customer->error) {
							$page->error = "Error transferring account: ".$customer->error.".  Please transfer manually.";
							app_log("Error transferring account: ".$customer->error,'debug',__FILE__,__LINE__);
						}
						else {
							$page->success = "Device and Account Transferred Successfully from "+$old_org->name;
							app_log("Transferred device and account ".$customer->code,'debug',__FILE__,__LINE__);
						}
					}
				}
				else {
					$page->success = "Device Transferred Successfully from "+$old_org->name;
					app_log("Transferred account ".$asset->code,'debug',__FILE__,__LINE__);
				}

				if ($page->success) {
					app_log("Recording event",'trace',__FILE__,__LINE__);
					$parameters = array(
						'asset_code'	=> $asset->code,
						'organization_code'	=> $organization->code,
						'reason'		=> $_REQUEST['reason'],
						'user_code'		=> $GLOBALS['_SESSION_']->customer->code,
						'timestamp'		=> date("c"),
						'message'		=> $page->success
					);
					$event = new \Event\Item();
					$event->add(
						'monitor',$parameters
					);
					if ($event->error) {
						app_log("Failed to record event: ".$event->error,'error',__FILE__,__LINE__);
						$page->error = "Transfer successed, but failed to record event";
					}
				}
			}
		}
	}

	$assetlist = new \Monitor\AssetList();
	$assets = $assetlist->find(array("_flat" => true));

	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find(array("_flat" => true));
?>