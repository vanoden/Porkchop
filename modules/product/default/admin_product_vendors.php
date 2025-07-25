<?=$page->showAdminPageInfo()?>

<div id="page_top_nav" style="margin-bottom: 20px;">
	<a href="/_spectros/admin_product/<?= $item->code ?>" class="button">Details</a>
	<a href="/_product/admin_product_prices/<?= $item->code ?>" class="button">Prices</a>
	<a href="/_product/admin_product_vendors/<?= $item->code ?>" class="button" disabled>Vendors</a>
	<a href="/_product/admin_images/<?= $item->code ?>" class="button">Images</a>
	<a href="/_product/admin_product_tags/<?= $item->code ?>" class="button">Tags</a>
</div>

<h3>Add A Vendor</h3>
<form id="addVendorForm" method="post" action="/_product/admin_product_vendors/<?= $item->code ?>">
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
	<input type="hidden" name="id" value="<?= $item->id ?>">
	<table class="tableBody">
		<tr>
			<th>Vendor</th>
			<th>Price</th>
			<th>Min Order</th>
			<th>Pack Qty</th>
			<th>Pack Unit</th>
			<th>Price Break Qty 1</th>
			<th>Price At Qty 1</th>
			<th>Price Break Qty 2</th>
			<th>Price At Qty 2</th>
		</tr>
		<tr>
			<td>
				<select name="vendor_id" class="value input">
					<option value="">Select Vendor</option>
					<?php foreach ($vendors as $vendor) { ?>
						<option value="<?= $vendor->id ?>"><?= htmlspecialchars($vendor->name) ?></option>
					<?php } ?>
				</select>
			</td>
			<td><input type="text" name="price" class="value input" style="width: 110px;" /></td>
			<td><input type="text" name="min_order" class="value input" style="width: 110px;" /></td>
			<td><input type="text" name="pack_quantity" class="value input" style="width: 110px;" /></td>
			<td><input type="text" name="pack_unit" class="value input" style="width: 110px;" /></td>
			<td><input type="text" name="price_break_quantity_1" class="value input" style="width: 110px;" /></td>
			<td><input type="text" name="price_at_quantity_1" class="value input" style="width: 110px;" /></td>
			<td><input type="text" name="price_break_quantity_2" class="value input" style="width: 110px;" /></td>
			<td><input type="text" name="price_at_quantity_2" class="value input" style="width: 80px;" /></td>
		</tr>
	</table>
	<input type="submit" name="addVendor" value="Add Vendor" class="button" />
</form>
<table class="tableBody">
	<tr>
		<th>Vendor</th>
		<th>Code</th>
		<th>Price</th>
		<th>Min Order</th>
		<th>Pack Qty</th>
		<th>Pack Unit</th>
		<th>Price Break Qty 1</th>
		<th>Price At Qty 1</th>
		<th>Price Break Qty 2</th>
		<th>Price At Qty 2</th>
		<th>Actions</th>
	</tr>
	<?php if (isset($item_vendors) && count($item_vendors) > 0) { ?>
		<?php foreach ($item_vendors as $vendor) {
			$vendorItem = new \Product\VendorItem();
			$vendorItem->get($vendor->id, $item->id);
			if ($vendorItem->error()) {
				print_r("Error retrieving vendor item: " . $vendorItem->error());
				continue;
			}
			?>
			<form method="post" action="/_product/admin_product_vendors/<?= $item->code ?>">
			<tr><td><span style="display: inline-block; width: 150px;"><?= htmlspecialchars($vendor->name) ?></span></td>
				<td><?= htmlspecialchars($vendor->code) ?></td>
				<td><input type="text" style="width: 100px; text-align: right" name="price" value="<?=$vendorItem->price?>"/></td>
				<td><input type="text" style="width: 100px; text-align: right" name="min_order" value="<?=$vendorItem->minimum_order?>"/></td>
				<td><input type="text" style="width: 100px; text-align: right" name="pack_quantity" value="<?=$vendorItem->pack_quantity?>"/></td>
				<td><input type="text" style="width: 100px; text-align: right" name="pack_unit" value="<?=$vendorItem->pack_unit?>"/></td>
				<td><input type="text" style="width: 100px; text-align: right" name="price_break_quantity_1" value="<?=$vendorItem->price_break_quantity_1?>"/></td>
				<td><input type="text" style="width: 100px; text-align: right" name="price_at_quantity_1" value="<?=$vendorItem->price_at_quantity_1?>"/></td>
				<td><input type="text" style="width: 100px; text-align: right" name="price_break_quantity_2" value="<?=$vendorItem->price_break_quantity_2?>"/></td>
				<td><input type="text" style="width: 100px; text-align: right" name="price_at_quantity_2" value="<?=$vendorItem->price_at_quantity_2?>"/></td>
				<td><div style="display: inline-block; width: 150px;">
					<input type="submit" name="updateVendor" value="Update" class="button" />
					<input type="hidden" name="vendor_id" value="<?= $vendor->id ?>" />
					<input type="hidden" name="item_id" value="<?= $item->id ?>" />
					<input type="submit" name="deleteVendor" value="Delete" class="button delete-button" onclick="return confirm('Are you sure you want to delete this vendor?');" />
					<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
				</div></td>
			</tr>
			</form>
		<?php } ?>
	<?php } else { ?>
		<tr><td colspan="4">No vendors found for this product.</td></tr>
	<?php } ?>
</table>