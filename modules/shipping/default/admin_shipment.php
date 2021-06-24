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
<div class="container">
	<span class="label">Shipped On</span>
	<span class="value"><?=$shipment->date_shipped?></span>
</div>
<div class="container">
	<span class="label">Vendor</span>
	<select class="value input" name="vendor_id">
		<option value="">Not Specified</option>
<?php	foreach ($vendors as $vendor) { ?>
		<option value="<?=$vendor->id?>"<?php	if ($shipment->vendor_id == $vendor->id) print " selected"; ?>><?=$vendor->name?></option>
<?php	} ?>
	</select>
</div>
<?php	foreach ($packages as $package) { ?>
<div class="table">
	<div class="tableRowHeader">
		<div class="tableCell">Package <?=$package->id?></div>
		<div class="tableCell">Tracking Number <?=$package->tracking_code?></div>
	</div>
</div>
<div class="table">
	<div class="tableRowHeader">
		<div class="tableCell">Quantity</div>
		<div class="tableCell">Product</div>
		<div class="tableCell">Serial Number</div>
	</div>
<?php	$items = $package->items();
	foreach ($items as $item) {
?>
	<div class="tableRow">
		<div class="tableCell"><?=$item->quantity?></div>
		<div class="tableCell"><?=$item->product()->code?></div>
		<div class="tableCell"><?=$item->serial_number?></div>
	</div>
<?php	} ?>
</div>
<?php	} ?>
