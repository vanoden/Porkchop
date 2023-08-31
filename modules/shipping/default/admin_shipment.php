<?=$page->showBreadcrumbs()?>
<div class="title">Shipment Detail</div>
<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<form method="post">
    <input type="hidden" name="id" value="<?=$shipment->id?>">
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    
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
            <div class="tableCell"><?=$shipment->document_number?></div>
            <div class="tableCell"><?=$shipment->status?></div>
            <div class="tableCell"><?=$shipment->send_contact()->full_name()?></div>
            <div class="tableCell"><?=$shipment->date_entered?></div>
            <?php if (!empty($shipment->date_shipped)) { ?>
                    <div class="tableCell"><?=$shipment->date_shipped?></div>
            <?php } ?>
            <div class="tableCell"><?=$shippingVendor->name?></div>	  
        </div>
    </div>

    <br/><br/>

    <?php	foreach ($packages as $package) { 
    ?>
        <div class="table">
	        <div class="tableRowHeader">
		        <div class="tableCell">Package</div>
		        <div class="tableCell">Tracking Number</div>
		        <div class="tableCell">Status</div>
		        <div class="tableCell">Received</div>
		        <div class="tableCell">Condition</div>
	        </div>
	        <div class="tableRow">
		        <div class="tableCell"><?=$package->number?></div>
		        <div class="tableCell"><?=$package->tracking_code?></div>
		        <div class="tableCell"><?=$package->status?></div>
		        <div class="tableCell"><?=$package->date_received?> by <?=$package->user_received()->full_name()?></div>
		        <div class="tableCell"><?=$package->condtion?></div>
	        </div>
        </div>
        <br/><br/>
        <div class="table">
	        <div class="tableRowHeader">
		        <div class="tableCell">Quantity</div>
		        <div class="tableCell">Product</div>
		        <div class="tableCell">Serial Number</div>
		        <div class="tableCell">Description</div>
	        </div>
        <?php	$items = $package->items();
		        foreach ($items as $item) {
        ?>
	        <div class="tableRow">
		        <div class="tableCell"><?=$item->quantity?></div>
		        <div class="tableCell"><?=$item->product()->code?></div>
		        <div class="tableCell"><?=$item->serial_number?></div>
		        <div class="tableCell"><?=$item->description?></div>
	        </div>
        <?php	} ?>
        </div>
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
    <?php if (!$shipment->shipped()) { ?>
	    <input type="submit" name="btn_shipped" class="button" value="Shipment Shipped" />
    <?php } ?>
	    <input type="submit" name="btn_lost" class="button" value="Shipment Lost" />
	    <input type="submit" name="btn_received" class="button" value="Shipment Received" />
    </div>
</form>
