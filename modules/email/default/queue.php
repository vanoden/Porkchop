<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Created</div>
		<div class="tableCell">Status</div>
		<div class="tableCell">Tried</div>
		<div class="tableCell">Tries</div>
		<div class="tableCell">To</div>
		<div class="tableCell">From</div>
	</div>
<?	foreach ($messages as $message) { ?>
	<div class="tableRow">
		<div class="tableCell"><?=$message->date_created?></div>
		<div class="tableCell"><?=$message->status?></div>
		<div class="tableCell"><?=$message->date_tried?></div>
		<div class="tableCell"><?=$message->tries?></div>
		<div class="tableCell"><?=$message->to?></div>
		<div class="tableCell"><?=$message->from?></div>
	</div>
<?	}	?>
</div>