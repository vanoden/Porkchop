<?= $page->showAdminPageInfo() ?>

<style>
  .tabs { display:flex; gap:6px; margin-bottom:20px; border-bottom:1px solid #ddd; }
  .tabs .tab { color:#555; background:#f4f4f4; border:1px solid #ddd; border-bottom:none; padding:8px 12px; border-top-left-radius:6px; border-top-right-radius:6px; text-decoration:none; }
  .tabs .tab:hover { background:#eee; }
  .tabs .tab.active { background:#fff; color:#222; font-weight:600; }
</style>
<?php $activeTab = 'tags'; ?>
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

<form method="post" action="/_product/admin_product_tags/<?= $item->code ?>">
<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
<input type="hidden" name="id" value="<?= $item->id ?>" />
<input type="hidden" id="removeTagId" name="removeTagId" value="" />
<input type="hidden" id="removeSearchTagId" name="removeSearchTagId" value="" />

<h3 style="display:inline;">Product Search Tags</h3>
<h4 style="display:inline;">(customer support knowledge center)</h4>
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 33%;">&nbsp;</div>
		<div class="tableCell" style="width: 33%;">Category</div>
		<div class="tableCell" style="width: 33%;">Search Tag</div>
	</div>
<?php
	foreach ($productSearchTags as $searchTag) {
?>
	<div class="tableRow">
		<div class="tableCell">
			<input type="button" onclick="removeSearchTagById('<?= $searchTag->id ?>')" name="removeSearchTag" value="Remove" class="button" />
		</div>
		<div class="tableCell">
			<?= $searchTag->category ?>
		</div>
		<div class="tableCell">
			<?= $searchTag->value ?>
		</div>
	</div>

<?php
	}
?>
	<br />
	<div class="tableRow">
		<div class="tableCell">
			<label>Category:</label>
			<input type="text" class="autocomplete" name="newSearchTagCategory" id="newSearchTagCategory" value="" placeholder="gas" />
			<ul id="categoryAutocomplete" class="autocomplete-list"></ul>
		</div>
		<div class="tableCell">
			<label>New Search Tag:</label>
			<input type="text" class="autocomplete" name="newSearchTag" id="newSearchTag" value="" placeholder="sulfuryl fluoride" />
			<ul id="tagAutocomplete" class="autocomplete-list"></ul>
		</div>
	</div>
	<div><input type="submit" name="addSearchTag" value="Add Search Tag" class="button" /></div>
</div>
</form>