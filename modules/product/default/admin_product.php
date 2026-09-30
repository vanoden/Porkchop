<script language="Javascript">
    function removeTagById(id) {
        document.getElementById('removeTagId').value = id;
        document.getElementById('productEdit').submit();
    }
</script>

<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->


<?php

use Product\productVisibilityRealm;

 $activeTab = 'details'; ?>
<?php require __DIR__ . '/admin_product_identity.php'; ?>
<?php require __DIR__ . '/admin_product_tabs.php'; ?>

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
		
<div class="form-actions filter-bar">
	<div class="button-group filter-bar__actions">
		<button type="submit" class="button" name="updateSubmit" id="updateSubmit" value="Update">Update</button>
	</div>
</div>
</form>
