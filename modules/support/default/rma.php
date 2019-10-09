<div id="support_rma">
	<div class="container">
		<span class="label">Number</span>
		<span class="value"><?=$rma->number()?></span>
	</div>
	<div class="container">
		<span class="label">Ticket</span>
		<span class="value"><a href="/_support/request_item/<?=$rma->item()->id?>"><?=$rma->item()->ticketNumber()?></a></span>
	</div>
	<div class="container">
		<span class="label">Contact</span>
		<span class="value"><?=$rma->item()->request->customer->full_name()?> - <?=$rma->item()->request->customer->organization->name?></span>
	</div>
	<div class="container">
		<span class="label">Approved By</span>
		<span class="value"><?=$rma->approvedBy->full_name()?></span>
	</div>
	<div class="container">
		<span class="label">Date Approved</span>
		<span class="value"><?=$rma->date_approved?></span>
	</div>
	<div class="container">
		<span class="label">Status</span>
		<span class="value"><?=$rma->status?></span>
	</div>
	<div class="container">
		<span class="label">Product</span>
		<span class="value"><?=$rma->item()->product->code?> - <?=$rma->item()->serial_number?></span>
	</div>
	<div class="container">
		<span class="label">Document</span>
		<span class="value"><? if ($rma->document()->exists()) { ?><a href="/_storage/file?id=<?=$rma->document()->id?>">View</a><? } ?></span>
	</div>
	<div class="container">
		<div class="label">Events</div>
		<div class="table">
			<div class="tableHeading">
				<div class="tableCell">Event Date</div>
				<div class="tableCell">Person</div>
				<div class="tableCell">Description</div>
			</div>
<?	foreach ($events as $event) { ?>
			<div class="tableRow">
				<div class="tableCell"><?=$event->date?></div>
				<div class="tableCell"><?=$event->person->full_name()?></div>
				<div class="tableCell"><?=$event->description?></div>
			</div>
<?	} ?>
		</div>
	</div>
</div>