<script>
	function updateReport() {
		document.forms[0].filtered.value = 1;
		document.forms[0].submit();
		return true;
	}
</script>
<div style="width: 756px;">
	<div class="breadcrumbs">
		<a href="/_support/requests">Support</a>
	</div>
	<?	if ($page->errorCount()) { ?>
	<div class="form_error"><?=$page->errorString()?></div>
	<? } ?>
	<?	if ($page->success) { ?>
	<div class="form_success"><?=$page->success?></div>
	<?	} ?>
</div>
<h2 style="display: inline-block; ">Tickets</h2>
<a class="button more" href="/_support/request_new">New Ticket</a>
<div style="width: 756px;">
	<form name="filterForm" method="get" action="/_support/request_items">
	<input type="hidden" name="filtered" value="<?=$_REQUEST['filtered']?>" />
	<span class="label">Status</span>
	<div class="checkbox-row">
		<input type="checkbox" name="status_new" value="1" onclick="updateReport()"<? if ($_REQUEST['status_new']) print " checked";?> />
		<span class="value">NEW</span>
		<input type="checkbox" name="status_active" value="1" onclick="updateReport()"<? if ($_REQUEST['status_active']) print " checked";?> />
		<span class="value">ACTIVE</span>
		<input type="checkbox" name="status_pending_customer" value="1" onclick="updateReport()"<? if ($_REQUEST['status_pending_customer']) print " checked";?> />
		<span class="value">PENDING CUSTOMER</span>
		<input type="checkbox" name="status_pending_vendor" value="1" onclick="updateReport()"<? if ($_REQUEST['status_pending_vendor']) print " checked";?> />
		<span class="value">PENDING VENDOR</span>
		<input type="checkbox" name="status_complete" value="1" onclick="updateReport()"<? if ($_REQUEST['status_complete']) print " checked";?> />
		<span class="value">COMPLETE</span>
		<input type="checkbox" name="status_closed" value="1" onclick="updateReport()"<? if ($_REQUEST['status_closed']) print " checked";?> />
		<span class="value">CLOSED</span>
	</div>
	</form>
</div>
<!--	Start First Row-->
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 9%;">Ticket #</div>
		<div class="tableCell" style="width: 17%;">Date Requested</div>
		<div class="tableCell" style="width: 20%;">Requestor</div>
		<div class="tableCell" style="width: 20%;">Organization</div>
		<div class="tableCell" style="width: 13%;">Product</div>
		<div class="tableCell" style="width: 12%;">Serial #</div>
		<div class="tableCell" style="width: 9%;">Status</div>
	</div> <!-- end row header -->
<?	foreach ($items as $item) { ?>
	<div class="tableRow">
		<div class="tableCell">
			<span class="value"><a href="/_support/request_item/<?=$item->id?>"><?=$item->ticketNumber()?></a></span>
		</div>
		<div class="tableCell">
			<span class="value"><?=$item->request->date_request?></span>
		</div>
		<div class="tableCell">
			<span class="value"><?=$item->request->customer->full_name()?></span>
		</div>
		<div class="tableCell">
			<span class="value"><?=$item->request->customer->organization->name?></span>
		</div>
		<div class="tableCell">
			<span class="value"><?=$item->product->code?></span>
		</div>
		<div class="tableCell">
			<span class="value"><?=$item->serial_number?></span>
		</div>
		<div class="tableCell">
			<span class="value"><?=ucwords(strtolower($item->status))?></span>
		</div>
	</div>
<?	} ?>
</div>