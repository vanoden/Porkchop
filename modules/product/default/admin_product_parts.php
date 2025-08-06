<?=$page->showAdminPageInfo()?>

<div id="page_top_nav" style="margin-bottom: 20px;">
	<a href="/_spectros/admin_product/<?= $item->code ?>" class="button">Details</a>
	<a href="/_product/admin_product_prices/<?= $item->code ?>" class="button">Prices</a>
	<a href="/_product/admin_product_vendors/<?= $item->code ?>" class="button" disabled>Vendors</a>
	<a href="/_product/admin_images/<?= $item->code ?>" class="button">Images</a>
	<a href="/_product/admin_product_tags/<?= $item->code ?>" class="button">Tags</a>
	<a href="/_product/admin_product_parts/<?= $item->code ?>" class="button">Parts</a>
</div>

<?php
	if (!empty($parts) && is_array($parts) && count($parts) > 0) {
?>
<form id="updateAssemblyForm" method="post" action="/_product/admin_product_parts/<?= $item->code ?>">
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
	<input type="hidden" name="part_id" value="<?= $part->id ?>">
	<div class="tableBody">
		<div class="tableRowHeader">
			<div class="tableCell">SKU</div>
			<div class="tableCell">Name</div>
			<div class="tableCell">Quantity</div>
			<div class="tableCell">Available</div>
			<div class="tableCell">Actions</div>
		</div>
<?php
		foreach ($parts as $part) {
			// Output each part's details
?>
		<div class="tableRow">
			<div class="tableCell">
				<span class="value"><?=$part->part()->code ?? ''?></span>
			</div>
			<div class="tableCell">
				<span class="value"><?=$part->part()->getMetadata("short_description") ?? ''?></span>
			</div>
			<div class="tableCell">
				<input type="text" name="quantity" class="value input" style="width: 110px;" value="<?=$part->quantity ?? 0?>"/>
			</div>
			<div class="tableCell">
				<span class="value"><?=$part->part()->onHand() ?? 0?></span>
			</div>
			<div class="tableCell">
				<input type="submit" name="updatePart" value="Update" class="button" />
				<input type="submit" name="deletePart" value="Delete" class="button delete-button" onclick="return confirm('Are you sure you want to delete this part?');" />
			</div>
		</div>
<?php
		}
?>
	</div>
</form>
<?php
	} else {
		echo "<p>No parts found for this product.</p>";
	}
?>
<form id="addPartForm" method="post" action="/_product/admin_product_parts/<?= $item->code ?>">
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
	<input type="hidden" name="id" value="<?= $item->id ?>">
	<div class="tableBody">
		<div class="tableRowHeader">
			<div class="tableCell">Part</div>
			<div class="tableCell">Quantity</div>
		</div>
		<div class="tableRow">
			<div class="tableCell">
				<select name="new_part_id" class="value input" style="width: 200px;">
					<option value="">Select a part...</option>
					<?php foreach ($products as $part): 
						if ($part->id == $item->id) continue; // Skip the current item
					?>
						<option value="<?= $part->id ?>"><?= $part->code . " - ".$part->getMetadata("short_description")?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="tableCell">
				<input type="text" name="new_quantity" class="value input" style="width: 110px;" value="1" />
			</div>
		</div>
	</div>
	<input type="submit" name="addPart" value="Add Part" class="button" />
</form>