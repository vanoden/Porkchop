<script language="Javascript">
    function removeSearchTagById(id) {
        document.getElementById('removeSearchTagId').value = id;
        document.getElementById('productEdit').submit();
    }
</script>

<!-- Autocomplete CSS and JS -->
<link href="/css/autocomplete.css" type="text/css" rel="stylesheet">
<script language="JavaScript" src="/js/autocomplete.js"></script>
<script language="JavaScript">
    // define existing categories and tags for autocomplete
    var existingCategories = <?= isset($uniqueTagsData['categoriesJson']) ? $uniqueTagsData['categoriesJson'] : '[]' ?>;
    var existingTags = <?= isset($uniqueTagsData['tagsJson']) ? $uniqueTagsData['tagsJson'] : '[]' ?>;
</script>

<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->


<?php

use Product\productVisibilityRealm;

 $activeTab = 'details'; ?>
<?php
    $__defImg = $item->getDefaultStorageImage();
    if ($__defImg && $__defImg->id) {
        $__thumb = "/api/media/downloadMediaImage?height=50&width=50&code=".$__defImg->code;
        $__title = htmlspecialchars($item->getMetadata('name') ?: $item->name ?: $item->code);
        echo '<div class="product-container">'
            . '<img src="'. $__thumb .'" alt="Default" class="product-thumb" />'
            . '<div class="product-title">'. $__title .'</div>'
            . '</div>';
    }
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

<form id="productEdit" name="productEdit" method="post" action="/_product/admin_product<?= $item->code ? '/' . $item->code : '' ?>">

    <input type="hidden" name="id" id="id" value="<?= $item->id ?>" />
    <input type="hidden" name="deleteImage" id="deleteImage" value="" />
    <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
    <input type="hidden" id="removeTagId" name="removeTagId" value="" />
    <input type="hidden" id="removeSearchTagId" name="removeSearchTagId" value="" />

    <table class="tableBody">
	<tr><th class="tableCell-width-25">Code/SKU</th><th class="tableCell-width-25">Type</th><th class="tableCell-width-25">Status</th><th class="tableCell-width-25">Name</th></tr>
	<tr><td>
<?php if ($item->id) { ?>
		<?=$item->code?>
<?php } else { ?>
		<input type="text" name="code" id="code" class="value input" value="<?= htmlspecialchars(($_REQUEST['code'] ?? '') ?: $item->code) ?>" />
<?php } ?>
		</td>
		<td><select name="type">
			<option value="">Select</option>
			<?php 
			$selected_type = ($_REQUEST['type'] ?? '') ?: $item->type;
			foreach ($item_types as $item_type) { ?>
				<option value="<?= $item_type ?>" <?php if ($item_type == $selected_type) print " selected"; ?>><?= $item_type ?></option>
			<?php } ?>
		</select></td>
		<td><select name="status">
			<option value="">Select</option>
			<?php 
			$selected_status = ($_REQUEST['status'] ?? '') ?: $item->status;
			?>
			<option value="ACTIVE" <?php if ($selected_status == 'ACTIVE') print " selected"; ?>>ACTIVE</option>
			<option value="HIDDEN" <?php if ($selected_status == 'HIDDEN') print " selected"; ?>>HIDDEN</option>
			<option value="DELETED" <?php if ($selected_status == 'DELETED') print " selected"; ?>>DELETED</option>
		</select></td>
		<td><input type="text" name="name" class="input-width-100" value="<?= htmlspecialchars(($_REQUEST['name'] ?? '') ?: $item->getMetadata('name')) ?>" /></td>
	</tr>
	<tr><th colspan="4">Description</th></tr>
	<tr><td colspan="4"><textarea name="description" id="description" class="value input textarea-width-100"><?= htmlspecialchars(($_REQUEST['description'] ?? '') ?: $item->getMetadata('description')) ?></textarea></td></tr>
	</table>
	<table class="tableBody">
	<tr><th>On Hand</th><th>On Hand Cost</th><th>Min Quantity</th><th>Max Quantity</th></tr>
	<tr>
		<td><?= $item->onHand() ?? 0 ?></td>
		<td><?= $item->onHandCost() ?? 0 ?></td>
		<td><input type="text" name="min_quantity" id="min_quantity" class="value input" value="<?= htmlspecialchars(($_REQUEST['min_quantity'] ?? '') !== '' ? $_REQUEST['min_quantity'] : ($item->minQuantity() ?? 0)) ?>" /></td>
		<td><input type="text" name="max_quantity" id="max_quantity" class="value input" value="<?= htmlspecialchars(($_REQUEST['max_quantity'] ?? '') !== '' ? $_REQUEST['max_quantity'] : ($item->maxQuantity() ?? 0)) ?>" /></td>
	</tr>
	<h3>Inventory and Vendors</h3>
	<tr><th>On Order</th><th>On Order Cost</th><th>Preferred Vendor</th><th>Total Purchased</th></tr>
	<tr>
		<td><?= $item->onOrder() ?? 0 ?></td>
		<td><?= $item->onOrderCost() ?? 0 ?></td>
		<td>
			<select name="default_vendor_id" id="default_vendor_id">
				<option value="">Select</option>
				<?php foreach ($vendors as $vendor) { ?>
					<option value="<?= $vendor->id ?>" <?php if ($item->defaultVendor() == $vendor->id) print " selected"; ?>><?= htmlspecialchars($vendor->name) ?></option>
				<?php } ?>
			</select>
		</td>
		<td><?= $item->totalPurchased() ?? 0 ?></td>
	</table>
	<h3>Visibility Settings</h3>
	<table class="tableBody">
	<tr><th>Marketing</th><th>Sales</th><th>Support Tools</th><th>Assembly</th><th>Administration</th></tr>
	<tr>
		<td><input type="checkbox" value="1" name="visibility_marketing" <?php if($item->getVisibility(\productVisibilityRealm::MARKETING)) print " checked"; ?> /></td>
		<td><input type="checkbox" value="1" name="visibility_sales" <?php if($item->getVisibility(\productVisibilityRealm::SALES)) print " checked"; ?> /></td>
		<td><input type="checkbox" value="1" name="visibility_support" <?php if($item->getVisibility(\productVisibilityRealm::SUPPORT)) print " checked"; ?> /></td>
		<td><input type="checkbox" value="1" name="visibility_assembly" <?php if($item->getVisibility(\productVisibilityRealm::ASSEMBLY)) print " checked"; ?> /></td>
		<td><input type="checkbox" value="1" disabled <?php if($item->getVisibility(\productVisibilityRealm::ADMINISTRATION)) print " checked"; ?> /></td>
	</tr>
	</table>
		
<div class="editSubmit button-bar floating">
	<input type="submit" class="button" value="Update" name="updateSubmit" id="updateSubmit" />
</div>
</form>
