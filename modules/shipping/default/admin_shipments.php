<span class="title">Shipments</span>

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

<div class="table">
	<div class="tableRowHeader">
		<div class="tableCell">Document</div>
		<div class="tableCell">Date Entered</div>
		<div class="tableCell">Status</div>
		<div class="tableCell">Shipping Vendor</div>
		<div class="tableCell">Source</div>
		<div class="tableCell">Destination</div>
	</div>
<?php	foreach ($shipments as $shipment) {
		$vendor = new \Shipping\Vendor($shipment->vendor_id);
		$sender = new \Register\Customer($shipment->send_contact_id);
		$receiver = new \Register\Customer($shipment->rec_contact_id);
 ?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_shipping/admin_shipment?id=<?=$shipment->id?>"><?=$shipment->document_number?></a></div>
		<div class="tableCell"><?=$shipment->date_entered?></div>
		<div class="tableCell"><?=$shipment->status?></div>
		<div class="tableCell"><?=$vendor->name?></div>
		<div class="tableCell"><?=$sender->full_name()?></div>
		<div class="tableCell"><?=$receiver->full_name()?></div>
	</div>
<?php	} ?>
</div>
