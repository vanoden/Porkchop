<div class="breadcrumbs">
   <a href="/_support/requests">Support Home</a> &gt; Support RMAs
</div>
<h2 style="display: inline-block;"><i class="fa fa-id-badge" aria-hidden="true"></i> Customer Product Registrations </h2>
<?php include(MODULES.'/support/partials/search_bar.php'); ?>
<div style="width: 100%;">

</div>

<form method="get">
<div class="table">
	<div class="tableRowHeader">
		<div class="tableCell">Status</div>
		<div class="tableCell">Organization</div>
		<div class="tableCell">Product</div>
		<div class="tableCell">Authorized After</div>
		<div class="tableCell">Authorized Before</div>
	</div>
	<div class="tableRow">
		<div class="tableCell">
			<select name="status" class="value input">
				<option value="NEW"<? if ($_REQUEST['status'] == "NEW") print " selected"; ?>>New</option>
				<option value="ACCEPTED"<? if ($_REQUEST['status'] == "ACCEPTED") print " selected"; ?>>Accepted</option>
				<option value="PRINTED"<? if ($_REQUEST['status'] == "PRINTED") print " selected"; ?>>Printed</option>
				<option value="CLOSED"<? if ($_REQUEST['status'] == "CLOSED") print " selected"; ?>>Closed</option>
			</select>
		</div>
		<div class="tableCell">
			<select name="organization_id" class="value input">
				<option value="">All</option>
<?	foreach ($organizations as $organization) { ?>
				<option value="<?=$organization->id?>"<? if ($organization->id == $_REQUEST['organization_id']) print " selected"; ?>><?=$organization->name?></option>
<?	} ?>
			</select>
		</div>
		<div class="tableCell">
			<select name="product_id" class="value input">
				<option value="">All</option>
<?	foreach ($products as $product) { ?>
				<option value="<?=$product->id?>"<? if ($product->id == $_REQUEST['product_id']) print " selected";?>><?=$product->code?></option>
<?	} ?>
			</select>
		</div>
		<div class="tableCell">
			<input type="text" name="date_start" class="input value" value="<?=$date_start?>" />
		</div>
		<div class="tableCell">
			<input type="text" name="date_end" class="input value" value="<?=$date_end?>" />
		</div>
	</div>
	<div class="tableRowFooter">
		<input type="submit" name="btn_filter" class="button" />
	</div>
</div>
</form>
<?	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?	} ?>
<div class="table">
	<div class="tableRowHeader">
		<div class="tableCell">RMA Number</div>
		<div class="tableCell">Status</div>
		<div class="tableCell">Organization</div>
		<div class="tableCell">Contact</div>
		<div class="tableCell">Product</div>
		<div class="tableCell">Serial Number</div>
		<div class="tableCell">Authorized</div>
		<div class="tableCell">By</div>
	</div>
<?	foreach ($rmas as $rma) {
		$item = $rma->item();
		$customer = $item->request()->customer;
?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_support/admin_rma/<?=$rma->code?>"><?=$rma->number()?></a></div>
		<div class="tableCell"><?=$rma->status?></div>
		<div class="tableCell"><?=$customer->organization->name?></div>
		<div class="tableCell"><?=$customer->full_name()?></div>
		<div class="tableCell"><?=$item->product->code?></div>
		<div class="tableCell"><?=$item->serial_number?></div>
		<div class="tableCell"><?=$rma->date_approved?></div>
		<div class="tableCell"><?=$rma->approvedBy()->full_name()?></div>
	</div>
<?	} ?>
</div>
