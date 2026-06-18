<?=$page->showAdminPageInfo()?>

<?php $activeTab = 'parts'; ?>
<?php require __DIR__ . '/admin_product_identity.php'; ?>
<?php require __DIR__ . '/admin_product_tabs.php'; ?>

<?php
    if (!empty($parts) && is_array($parts) && count($parts) > 0) {
?>
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
            <form method="post" action="/_product/admin_product_parts/<?= $item->code ?>">
                <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
                <input type="hidden" name="part_id" value="<?= $part->id ?>">
                <div class="tableCell">
                    <input type="text" name="quantity" class="value input input-width-110" value="<?=$part->quantity ?? 0?>"/>
                </div>
                <div class="tableCell">
                    <span class="value"><?=$part->part()->onHand() ?? 0?></span>
                </div>
                <div class="tableCell">
                    <input type="submit" name="updatePart" value="Update" class="button" />
                    <input type="submit" name="deletePart" value="Delete" class="button delete-button" onclick="return confirm('Are you sure you want to delete this part?');" />
                </div>
            </form>
		</div>
<?php
		}
?>
    </div>
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
				<select name="new_part_id" class="value input input-width-200">
					<option value="">Select a part...</option>
					<?php foreach ($products as $part): 
						if ($part->id == $item->id) continue; // Skip the current item
					?>
						<option value="<?= $part->id ?>"><?= $part->code ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="tableCell">
				<input type="text" name="new_quantity" class="value input input-width-110" value="1" />
			</div>
		</div>
	</div>
	<input type="submit" name="addPart" value="Add Part" class="button" />
</form>
