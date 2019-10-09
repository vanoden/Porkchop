<div class="table">
	<div class="tableHeading">
		<div class="tableCell">RMA Number</div>
		<div class="tableCell">Status</div>
		<div class="tableCell">Organization</div>
		<div class="tableCell">Contact</div>
		<div class="tableCell">Product</div>
		<div class="tableCell">Serial Number</div>
	</div>
<?	foreach ($rmas as $rma) {
		$item = $rma->item();

?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_support/rma/<?=$rma->code?>"><?=$rma->number()?></a></div>
		<div class="tableCell"><?=$rma->status?></div>
		<div class="tableCell"><?=$item->request()->customer->organization->name?></div>
		<div class="tableCell"><?=$item->request()->customer->full_name()?></div>
		<div class="tableCell"><?=$item->product->name?></div>
		<div class="tableCell"><?=$item->serial_number?></div>
	</div>
<?	} ?>
</div>
