<?=$page->showAdminPageInfo()?>

<?php $activeTab = 'prices'; ?>
<?php
    $__defImg = $item->getDefaultStorageImage();
    if ($__defImg && $__defImg->id) {
        $thumb = "/api/media/downloadMediaImage?height=50&width=50&code=".$__defImg->code;
        $title = htmlspecialchars($item->getMetadata('name') ?: $item->name ?: $item->code);
    }
?>
<div class="product-container">
    <img src="<?=$thumb?>" alt="Default" class="product-thumb" />
    <div class="product-title"><?=$title?></div>
</div>
<?php
?>
<div class="tabs">
    <a href="/_product/admin_product/<?= $item->code ?>" class="tab <?= $activeTab==='details'?'active':'' ?>">Details</a>
    <a href="/_product/admin_product_prices/<?= $item->code ?>" class="tab <?= $activeTab==='prices'?'active':'' ?>">Prices</a>
    <a href="/_product/admin_product_vendors/<?= $item->code ?>" class="tab <?= $activeTab==='vendors'?'active':'' ?>">Vendors</a>
    <a href="/_product/admin_images/<?= $item->code ?>" class="tab <?= $activeTab==='images'?'active':'' ?>">Images</a>
    <a href="/_product/admin_product_tags/<?= $item->code ?>" class="tab <?= $activeTab==='tags'?'active':'' ?>">Tags</a>
    <a href="/_product/admin_product_parts/<?= $item->code ?>" class="tab <?= $activeTab==='parts'?'active':'' ?>">Parts</a>
    <a href="/_product/admin_product_metadata/<?= $item->code ?>" class="tab <?= $activeTab==='metadata'?'active':'' ?>">Metadata</a>
    <a href="/_product/audit_log/<?= $item->code ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
</div>

<form method="post" action="/_product/admin_product_prices/<?= $item->code ?>">
<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
<input type="hidden" name="id" value="<?= $item->id ?>">

<h3>Add Price</h3>
	<table class="body">
	<tr><th>Date Active</th>
		<th>Status</th>
		<th>Amount</th>
	</tr>
	<tr><td><input type="text" name="new_price_date" value="now" /></td>
		<td><select name="new_price_status">
				<option value="ACTIVE">ACTIVE</option>
				<option value="INACTIVE">INACTIVE</option>
			</select>
		</td>
		<td><input type="text" name="new_price_amount" value="0.00" /></td>
	</tr>
</table>

<h3>Prices</h3>
<table class="body">
<tr><th>Date Active</th>
	<th>Status</th>
	<th>Amount</th>
</tr>
<?php if (!empty($prices)) { ?>
	<?php foreach ($prices as $price) { ?>
	<tr><td class="value"><?= $price->date_active ?></td>
		<td class="value"><?= $price->status ?></td>
		<td class="value"><?= $price->amount ?></td>
	</tr>
	<?php } ?>
<?php } else { ?>
	<tr><td colspan="3" class="value">No prices found</td></tr>
<?php } ?>
</table>

<h3>Price Audit Info</h3>
<table class="body">
<tr><th>User</th>
	<th>Date</th>
	<th>Note</th>
</tr>
<?php if (!empty($auditedPrices)) { ?>
	<?php foreach ($auditedPrices as $priceAudit) { ?>
	<tr><td class="value">
	<?php $customer = new Register\Customer($priceAudit->user_id); ?>
			<?= $customer->first_name ?> <?= $customer->last_name ?>
		</td>
		<td class="value"><?= $priceAudit->date_updated ?></td>
		<td class="value"><?= stripslashes($priceAudit->note) ?></td>
	</tr>
	<?php } ?>
<?php } else { ?>
	<tr><td colspan="3" class="value">No price audit records found</td></tr>
<?php } ?>
</table>

<div class="editSubmit button-bar floating">
	<input type="submit" class="button" value="Update" name="updateSubmit" id="updateSubmit" />
</div>
</form>
