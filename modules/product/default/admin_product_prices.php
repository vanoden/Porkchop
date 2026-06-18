<?=$page->showAdminPageInfo()?>

<?php $activeTab = 'prices'; ?>
<?php require __DIR__ . '/admin_product_identity.php'; ?>
<?php require __DIR__ . '/admin_product_tabs.php'; ?>

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

<div class="form-actions filter-bar">
	<div class="button-group filter-bar__actions">
		<button type="submit" class="button" name="updateSubmit" id="updateSubmit" value="Update">Update</button>
	</div>
</div>
</form>
