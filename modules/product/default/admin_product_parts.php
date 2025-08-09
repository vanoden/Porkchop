<?=$page->showAdminPageInfo()?>

<style>
  .tabs { display:flex; gap:6px; margin-bottom:20px; border-bottom:1px solid #ddd; }
  .tabs .tab { color:#555; background:#f4f4f4; border:1px solid #ddd; border-bottom:none; padding:8px 12px; border-top-left-radius:6px; border-top-right-radius:6px; text-decoration:none; }
  .tabs .tab:hover { background:#eee; }
  .tabs .tab.active { background:#fff; color:#222; font-weight:600; }
</style>
<?php $activeTab = 'parts'; ?>
<?php
    $__defImg = $item->getDefaultStorageImage();
    if ($__defImg && $__defImg->id) {
        $__thumb = "/api/media/downloadMediaImage?height=50&width=50&code=".$__defImg->code;
        $__title = htmlspecialchars($item->getMetadata('name') ?: $item->name ?: $item->code);
        echo '<div style="margin:6px 0 8px 0; display:flex; align-items:center; gap:8px;">'
            . '<img src="'. $__thumb .'" alt="Default" style="width:50px;height:50px;border:1px solid #ddd;border-radius:3px;object-fit:cover;" />'
            . '<div style="font-weight:600;">'. $__title .'</div>'
            . '</div>';
    }
?>
<div class="tabs">
    <a href="/_spectros/admin_product/<?= $item->code ?>" class="tab <?= $activeTab==='details'?'active':'' ?>">Details</a>
    <a href="/_product/admin_product_prices/<?= $item->code ?>" class="tab <?= $activeTab==='prices'?'active':'' ?>">Prices</a>
    <a href="/_product/admin_product_vendors/<?= $item->code ?>" class="tab <?= $activeTab==='vendors'?'active':'' ?>">Vendors</a>
    <a href="/_product/admin_images/<?= $item->code ?>" class="tab <?= $activeTab==='images'?'active':'' ?>">Images</a>
    <a href="/_product/admin_product_tags/<?= $item->code ?>" class="tab <?= $activeTab==='tags'?'active':'' ?>">Tags</a>
    <a href="/_product/admin_product_parts/<?= $item->code ?>" class="tab <?= $activeTab==='parts'?'active':'' ?>">Parts</a>
    <a href="/_spectros/admin_asset_sensors/<?= $item->code ?>" class="tab <?= $activeTab==='sensors'?'active':'' ?>">Sensors</a>
    <a href="/_product/audit_log/<?= $item->code ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
</div>

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
                    <input type="text" name="quantity" class="value input" style="width: 110px;" value="<?=$part->quantity ?? 0?>"/>
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