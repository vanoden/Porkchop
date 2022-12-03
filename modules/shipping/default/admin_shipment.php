<span class="title">Admin Shipment</span>

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
    <div class="container">
	    <span class="label">Code</span>
	    <span class="value"><?=$shipment->code?></span>
    </div>
    <div class="container">
	    <span class="label">Document</span>
	    <span class="value"><?=$shipment->document_number?></span>
    </div>
    <div class="container">
	    <span class="label">Status</span>
	    <span class="value"><?=$shipment->status?></span>
    </div>
    <div class="container">
	    <span class="label">Entered By</span>
	    <span class="value"><?=$shipment->send_contact()->full_name()?></span>
    </div>
    <div class="container">
	    <span class="label">Entered On</span>
	    <span class="value"><?=$shipment->date_entered?></span>
    </div>
    <?php if (!empty($shipment->date_shipped)) { ?>
        <div class="container">
	        <span class="label">Shipped On</span>
	        <span class="value"><?=$shipment->date_shipped?></span>
        </div>
    <?php } ?>
    <div class="container">
	    <span class="label">Vendor</span>
	    <?=$shippingVendor->name?>
    </div>
    <?php	foreach ($packages as $package) { ?>
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
        <br>
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
    <?php } ?>
    <div class="form_footer">
	    <input type="submit" name="btn_shipped" class="button" value="Shipment Shipped" />
	    <input type="submit" name="btn_lost" class="button" value="Shipment Lost" />
	    <input type="submit" name="btn_received" class="button" value="Shipment Received" />
    </div>
</form>
