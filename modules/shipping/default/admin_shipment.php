<?=$page->showAdminPageInfo()?>
<script language="JavaScript">
    function shipPackage(packageId) {
        document.forms[0].package_id.value = packageId;
        document.forms[0].action_type.value = 'ship';
        document.forms[0].submit();
    }
    function receivePackage(packageId) {
        document.forms[0].package_id.value = packageId;
        document.forms[0].action_type.value = 'receive';
        document.forms[0].submit();
    }
    function lostPackage(packageId) {
        document.forms[0].package_id.value = packageId;
        document.forms[0].action_type.value = 'lost';
        document.forms[0].submit();
    }
    function closeShipment() {
        document.forms[0].action_type.value = 'close';
        document.forms[0].submit();
    }
</script>
<form method="post">
    <input type="hidden" name="id" value="<?=$shipment->id?>">
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    <input type="hidden" name="package_id" />
    <input type="hidden" name="action_type" />
    
    <div class="table">
        <div class="tableRowHeader">
            <div class="tableCell">Code</div>
            <div class="tableCell">Document</div>
            <div class="tableCell">Status</div>
            <div class="tableCell">Entered By</div>
            <div class="tableCell">Entered On</div>
            <?php if (!empty($shipment->date_shipped)) { ?>
                    <div class="tableCell">Shipped On</div>
            <?php } ?>
            <div class="tableCell">Vendor</div>	        
        </div>   
        <div class="tableRow">
            <div class="tableCell"><?=$shipment->code?></div>
            <div class="tableCell"><a href="<?=$object_link?>"><?=$shipment->document_number?></a></div>
            <div class="tableCell"><?=$shipment->status?></div>
            <div class="tableCell"><?=$shipment->send_contact()->full_name()?></div>
            <div class="tableCell"><?=$shipment->date_entered?></div>
            <?php if (!empty($shipment->date_shipped)) { ?>
                    <div class="tableCell"><?=$shipment->date_shipped?></div>
            <?php } ?>
            <div class="tableCell"><?=$shippingVendor->name?></div>	  
        </div>
    </div>

    <?php	foreach ($packages as $package) { ?>
        <h3>Package <?=$package->number?></h3>
        <div class="table">
	        <div class="tableRowHeader">
		        <div class="tableCell">Tracking Number</div>
		        <div class="tableCell">Status</div>
		        <div class="tableCell">Received</div>
		        <div class="tableCell">Condition</div>
	        </div>
	        <div class="tableRow">
		        <div class="tableCell"><?=$package->tracking_code?></div>
		        <div class="tableCell"><?=$package->status?></div>
		        <div class="tableCell"><?=$package->date_received?> by <?=$package->user_received()->full_name()?></div>
    <?php   if ($package->status == 'READY') { ?>
                <div class="tableCell">N/A</div>
    <?php   } elseif ($package->status == 'RECEIVED') { ?>
		        <div class="tableCell"><?=$package->condition?></div>
    <?php   } else { ?>
		        <div class="tableCell">
                    <select name="package_condition[<?=$package->id?>]">
                        <option value="OK">Ok</option>
                        <option value="DAMAGED">Damaged</option>
                    </select>
                </div>
    <?php   } ?>
	        </div>
        </div>
        <h3>Package <?=$package->number?> Items</h3>
        <div class="table">
	        <div class="tableRowHeader">
		        <div class="tableCell">Quantity</div>
		        <div class="tableCell">Product</div>
		        <div class="tableCell">Serial Number</div>
		        <div class="tableCell">Description</div>
                <div class="tableCell">Received</div>
	        </div>
        <?php	$items = $package->items();
		        foreach ($items as $item) {
        ?>
	        <div class="tableRow">
		        <div class="tableCell"><?=$item->quantity?></div>
		        <div class="tableCell"><?=$item->product()->code?></div>
		        <div class="tableCell"><?=$item->serial_number?></div>
		        <div class="tableCell"><?=$item->description?></div>
                <div class="tableCell">
        <?php   if ($package->status == "READY") { ?>
                N/A
        <?php   } elseif ($package->status == "RECEIVED") { ?>
                <?=$item->condition?>
        <?php   } else { ?>
                <select name="item_condition[<?=$item->id?>]">
                    <option value="OK">Ok</option>
                    <option value="DAMAGED">Damaged</option>
                    <option value="MISSING">Missing</option>
                </select>
        <?php   } ?>
                </div>
	        </div>
        <?php	} ?>
        </div>
        <?php   if ($package->status == "READY") { ?>
        <input type="button" name="btn_ship_package" class="button" value="Ship Package <?=$package->number?>" onclick="shipPackage(<?=$package->id?>)" />
        <?php   } elseif ($package->status != "RECEIVED") { ?>
        <input type="button" name="btn_receive_package" class="button" value="Receive Package <?=$package->number?>" onclick="receivePackage(<?=$package->id?>)" />
        <input type="button" name="btn_lost_package" class="button" value="Package <?=$package->number?> Lost" onclick="lostPackage(<?=$package->id?>)" />
        <?php   } ?>
    <?php	} ?>
    <?php if (empty($shipment->vendor_id)) { ?>
	    <span class="label">Shipping Vendor</span>
	    <select class="value input" name="vendor_id">
		    <option value="">Not Specified</option>
    <?php	foreach ($vendors as $vendor) { ?>
		        <option value="<?=$vendor->id?>"<?php	if ($shipment->vendor_id == $vendor->id) print " selected"; ?>><?=$vendor->name?></option>
    <?php	} ?>
	    </select>
    <?php } else { ?>
        <input type="hidden" name="vendor_id" value="<?=$shipment->vendor_id?>">
    <?php } ?>
    <div class="form_footer">
    <?php if ($shipment->ok_to_close()) { ?>
	    <input type="button" name="btn_close" class="button" value="Close Shipment" onclick="closeShipment()" />
    <?php  } ?>
    </div>
    <h3>Addresses</h3>
    <div class="table">
        <div class="tableRowHeader">
            <div class="tableCell">Ship From</div>
            <div class="tableCell">Ship To</div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <a class="value"><a href="/_register/admin_location?id=<?=$from_location->id?>"><?=$from_location->name?></a><br>
                <span class="value"><?=$from_location->HTMLBlockFormat()?></span>
            </div>
            <div class="tableCell">
                <a class="value"><a href="/_register/admin_location?id=<?=$to_location->id?>"><?=$to_location->name?></a><br>
                <span class="value"><?=$to_location->HTMLBlockFormat()?></span>
            </div>
        </div>
    </div>
</form>
