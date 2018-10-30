<script language="Javascript">
	function goForm(selectedForm) {
		document.requestForm.action = '/_support/request_'+selectedForm;
		document.requestForm.submit();
	}
</script>


<div style="width: 756px;">
	<div class="breadcrumbs">
		<a href="/_support/requests">Support</a>
		<a href="/_support/requests">Requests</a>
	</div>
	<?	if ($page->errorCount()) { ?>
	<div class="form_error"><?=$page->errorString()?></div>
	<? } ?>
	<?	if ($page->success) { ?>
	<div class="form_success"><?=$page->success?></div>
	<?	} ?>
	<form name="requestForm" method="post">
	<input type="hidden" name="request_id" value="<?=$request->id?>" />
	<h2>Request: <span><?=$request->code?></span></h2>
		
		
<!--	Start First Row-->
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 25%;">Date Requested</div>
		<div class="tableCell" style="width: 25%;">Requestor</div>
		<div class="tableCell" style="width: 25%;">Organization</div>
		<div class="tableCell" style="width: 13%;">Type</div>
		<div class="tableCell" style="width: 12%;">Status</div>
	</div> <!-- end row header -->
	<div class="tableRow">
		<div class="tableCell">
			<span class="value"><?=$request->date_request?></span>
		</div>
		<div class="tableCell">
			<span class="value"><?=$request->customer->full_name()?></span>
		</div>
		<div class="tableCell">
			<span class="value"><?=$request->customer->organization->name?></span>
		</div>
		<div class="tableCell">
			<span class="value"><?=ucwords(strtolower($request->type))?></span>
		</div>
		<div class="tableCell">
			<span class="value"><?=ucwords(strtolower($request->status))?></span>
		</div>
	</div>
</div>
<div class="container">
	<input type="submit" name="btn_cancel" class="button" value="Cancel Request" />
<?	if (in_array($request->status,array('CLOSED','COMPLETE','CANCELLED'))) { ?>
	<input type="submit" name="btn_reopen" class="button" value="Reopen Request" />
<?	} else { ?>
	<input type="submit" name="btn_close" class="button" value="Close Request" />
<?	} ?>
</div>
<!--End first row-->		

<h3>Request Items</h3>
<!--	Start Request Item-->
<?	foreach ($items as $item) { ?>
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 10%;">Line</div>
		<div class="tableCell" style="width: 25%;">Product</div>
		<div class="tableCell" style="width: 25%;">Serial</div>
		<div class="tableCell" style="width: 20%;">Status</div>
	</div> <!-- end row header -->
	<div class="tableRow">
		<div class="tableCell">
			<a href="/_support/request_item/<?=$item->id?>"><?=$item->line?></a>
		</div>
		<div class="tableCell">
			<?=$item->product->code?>
		</div>
		<div class="tableCell">
			<?=$item->serial_number?>
		</div>
		<div class="tableCell">
			<?=$item->status?>
		</div>
	</div>
</div>
		
<div class="tableBody min-tablet marginBottom_20">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 100%;">Description</div>
	</div> <!-- end row header -->
	<div class="tableRow">
		<div class="tableCell">
			<?=$item->description?>
		</div>
	</div>
</div>		
<?	} ?>	
<!--End Request Item -->	
		
<h3>Add Item</h3>
		
<!--	Start Request Item-->
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 30%;">Product ID</div>
		<div class="tableCell" style="width: 30%;">Serial Number</div>
		<div class="tableCell" style="width: 20%;">Status</div>
	</div> <!-- end row header -->
	<div class="tableRow">
		<div class="tableCell">
			<select name="product_id" class="value input">
				<option value="">N/A</option>
				<?	foreach ($products as $product) { ?>
				<option value="<?=$product->id?>"><?=$product->code?></option>
				<?	} ?>
		</select>
		</div>
		<div class="tableCell">
			<input type="text" name="serial_number" class="value input" />
		</div>
		<div class="tableCell">
			<select name="item_status" class="value input">
				<?	foreach ($statuses as $status) { ?>
				<option value="<?=$status?>"><?=ucwords(strtolower($status))?></option>
				<?	} ?>
			</select>
		</div>
	</div>
</div>
		
<div class="tableBody min-tablet marginBottom_20">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 100%;">Description</div>
	</div> <!-- end row header -->
	<div class="tableRow">
		<div class="tableCell">
			<textarea class="value input" name="item_description" style="width: 100%"></textarea>
		</div>
	</div>
	<div class="tableRow button-bar">
		<input type="submit" name="btn_add_item" class="button" value="Add Item" />
	</div>
</div>		
<!--End Request Item -->		

	</form>
</div>