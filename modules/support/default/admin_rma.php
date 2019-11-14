<h2>RMA Details</h2>
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Code</div>
		<div class="tableCell">Approved By</div>
		<div class="tableCell">Approved On</div>
		<div class="tableCell">Status</div>
	</div>
	<div class="tableRow">
		<div class="tableCell"><?=$rma->code?></div>
		<div class="tableCell"><?=$rma->approvedBy()->full_name()?></div>
		<div class="tableCell"><?=$rma->date_approved?></div>
		<div class="tableCell"><?=$rma->status?></div>
	</div>
</div>

<h2>Ticket Details</h2>
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Requested By</div>
		<div class="tableCell">Requested On</div>
		<div class="tableCell">Ticket Number</div>
		<div class="tableCell">Assigned To</div>
	</div>
	<div class="tableRow">
		<div class="tableCell"><a href="/_register/account/<?=$customer->code?>"><?=$customer->full_name()?></a></div>
		<div class="tableCell"><?=$item->request()->date_request?></div>
		<div class="tableCell"><a href="/_support/request_item/<?=$item->id?>"><?=$item->ticketNumber()?></a></div>
		<div class="tableCell"><a href="/_register/account/<?=$tech->id?>"><?=$tech->full_name()?></a></div>
	</div>
	<div class="tableRowHeader">
		<div class="tableCell">Status</div>
		<div class="tableCell">Line</div>
		<div class="tableCell">Product</div>
		<div class="tableCell">Serial Number</div>
	</div>
	<div class="tableRow">
		<div class="tableCell"><?=$item->status?></div>
		<div class="tableCell"><?=$item->line?></div>
		<div class="tableCell"><a href="/_product/edit/<?=$item->product->code?>"><?=$item->product->code?></a></div>
		<div class="tableCell"><a href="/_monitor/admin_details/<?=$item->serial_number?>/<?=$item->product->code?>"><?=$item->serial_number?></a></div>
	</div>
</div>

<h2>Shipments</h2>
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Status</div>
		<div class="tableCell">Shipped</div>
		<div class="tableCell">Shipper</div>
	</div>
<?	foreach ($shipments as $shipment) { ?>
	<div class="tableRow">
		<div class="tableCell"><?=$shipment->status?></div>
		<div class="tableCell"><?=$shipment->date_shipped?></div>
		<div class="tableCell"><?=$shipment->vendor?></div>
	</div>
<?	} ?>
</div>
