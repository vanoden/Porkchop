<?php
/** Shared product name/thumbnail header for admin product pages. Requires $item. */
$productDisplayName = htmlspecialchars($item->getMetadata('name') ?: $item->name ?: $item->code);
$productDefaultImage = $item->getDefaultStorageImage();
?>
<div class="product-container">
<?php if ($productDefaultImage && $productDefaultImage->id) { ?>
	<img src="/api/media/downloadMediaImage?height=50&width=50&code=<?= htmlspecialchars($productDefaultImage->code, ENT_QUOTES, 'UTF-8') ?>" alt="" class="product-thumb" />
<?php } ?>
	<div class="product-title"><?= $productDisplayName ?></div>
</div>
