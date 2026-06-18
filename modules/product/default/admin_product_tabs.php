<?php
/** Shared tab nav for admin product pages. Requires $activeTab and $item. */
if (!isset($activeTab)) {
	$activeTab = 'details';
}
$productCode = htmlspecialchars((string)$item->code, ENT_QUOTES, 'UTF-8');
?>
<div class="tabs">
	<a href="/_spectros/admin_product/<?= $productCode ?>" class="tab <?= $activeTab === 'details' ? 'active' : '' ?>">Details</a>
	<a href="/_product/admin_product_prices/<?= $productCode ?>" class="tab <?= $activeTab === 'prices' ? 'active' : '' ?>">Prices</a>
	<a href="/_product/admin_product_vendors/<?= $productCode ?>" class="tab <?= $activeTab === 'vendors' ? 'active' : '' ?>">Vendors</a>
	<a href="/_product/admin_images/<?= $productCode ?>" class="tab <?= $activeTab === 'images' ? 'active' : '' ?>">Images</a>
	<a href="/_product/admin_product_tags/<?= $productCode ?>" class="tab <?= $activeTab === 'tags' ? 'active' : '' ?>">Tags</a>
	<a href="/_product/admin_product_parts/<?= $productCode ?>" class="tab <?= $activeTab === 'parts' ? 'active' : '' ?>">Parts</a>
	<a href="/_spectros/admin_asset_sensors/<?= $productCode ?>" class="tab <?= $activeTab === 'sensors' ? 'active' : '' ?>">Sensors</a>
	<a href="/_product/admin_product_metadata/<?= $productCode ?>" class="tab <?= $activeTab === 'metadata' ? 'active' : '' ?>">Metadata</a>
	<a href="/_product/audit_log/<?= $productCode ?>" class="tab <?= $activeTab === 'audit' ? 'active' : '' ?>">Audit Log</a>
</div>
