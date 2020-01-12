<div class="container">
	<span class="label">Code</span>
	<span class="value"><?=$shipment->code?></span>
</div>
<div class="container">
	<span class="label">Document</span>
	<span class="value"><?=$shipment->document_number?></span>
</div>
<div class="container">
	<span class="label">Vendor</span>
	<select class="value input" name="vendor_id">
<?	foreach ($vendors as $vendor) { ?>
		<option value="<?=$vendor->id?>"<? if ($shipment->vendor_id == $vendor->id) print " selected"; ?>><?=$vendor->name?></option>
<?	} ?>
	</select>
</div>
<?	foreach ($packages as $package) { ?>
<div class="table">
	<div class="tableRowHeader">
		<div class="tableCell">Package <?=$package->id?></div>
		<div class="tableCell">Tracking Number <?=$package->tracking_number?></div>
	</div>
</div>
<div class="table">
	<div class="tableRowHeader">
		<div class="tableCell">Quantity</div>
		<div class="tableCell">Product</div>
		<div class="tableCell">Serial Number</div>
	</div>
<?	$items = $package->items();
	foreach ($items as $item) {
?>
	<div class="tableRow">
		<div class="tableCell"><?=$item->quantity?></div>
		<div class="tableCell"><?=$item->product()->code?></div>
		<div class="tableCell"><?=$item->serial_number?></div>
	</div>
<?	} ?>
</div>
<? } ?>
