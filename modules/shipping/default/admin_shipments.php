<?=$page->showAdminPageInfo()?>
<script src="/js/sort.js"></script>
<script language="javascript">
	var sortFields = ['document_number','date_entered','status'];
	var defaultSortField = 'date_entered';
	var formName = 'shippingListForm';
</script>
<script src="/js/sortHelper.js"></script>

<form id="shippingListForm" name="shippingListForm">
<input type="hidden" name="filtered" value="<?=isset($_REQUEST['filtered']) ? htmlspecialchars($_REQUEST['filtered']) : ''?>" />
<input id="sort_field" type="hidden" name="sort_field" value="<?=isset($_REQUEST['sort_field']) ? htmlspecialchars($_REQUEST['sort_field']) : ''?>" />
<input id="sort_direction" type="hidden" name="sort_direction" value="<?=(isset($_REQUEST['sort_direction']) && $_REQUEST['sort_direction'] == 'desc') ? 'asc': 'desc';?>" /> 
<div class="table">
	<div class="tableRowHeader">
		<div id="document_number-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_field').value = 'document_number'; updateReport()">Document</div>
		<div id="date_entered-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_field').value = 'date_entered'; updateReport()">Date Entered</div>
		<div id="status-sortable-column" class="tableCell sortableHeader" onclick="document.getElementById('sort_field').value = 'status'; updateReport()">Status</div>
		<div id="vendor-sortable-column" class="tableCell">Shipping Vendor</div>
		<div id="source-sortable-column" class="tableCell">Source</div>
		<div id="destination-sortable-column" class="tableCell">Destination</div>
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
</form>

<!-- Start pagination -->
<div class="pagination" id="pagination">
    <?=$pagination->renderPages()?>
</div>
<!-- End pagination -->