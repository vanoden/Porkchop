<script language="Javascript" src="/js/product.js" defer></script>
<script language="Javascript">
	var csrfToken = '<?=$GLOBALS['_SESSION_']->getCSRFToken()?>';

	function initImageSelectWizard() {
		var repositoryCode = "<?=$repository->code?>";
		var path = "/spectros_product_image";
		var imageSelectUrl = "/_media/image_select?repository_code=" + repositoryCode + "&path=" + path;
		var childWindow = open(imageSelectUrl, "imageselect", 'resizable=no,width=500,height=500,location=no,menubar=no,scrollbars=yes,status=no,toolbar=no');
		if (childWindow && childWindow.opener == null) childWindow.opener = self;
	}

	function endImageSelectWizard(code) {
		if (!code) return;
		if (typeof Item === 'undefined') {
			console.error("Item object is not defined");
			return;
		}
		var product = Object.create(Item);
		product.get('<?=$item->code?>');
		if (product.error) {
			console.error("Error loading product: " + product.error);
			return;
		}
		if (product.addImage(code)) {
			window.location.reload();
		} else {
			console.error("Error adding image: " + product.error);
		}
	}

	function updateDefaultImage(imageId) {
		document.getElementById('default_image_id').value = imageId;
		document.getElementById('updateImage').value = 'true';
		document.getElementById('imagesForm').submit();
	}

	function removeImageFromProduct(imageId) {
		if (confirm('Do you want to delete this product image?')) {
			document.getElementById('deleteImage').value = imageId;
			document.getElementById('imagesForm').submit();
		}
	}
</script>

<?= $page->showAdminPageInfo() ?>

<?php $activeTab = 'images'; ?>
<?php require __DIR__ . '/admin_product_identity.php'; ?>
<?php require __DIR__ . '/admin_product_tabs.php'; ?>

<?php if ($repository->id) { ?>
<form method="post" action="/_product/admin_images/<?= $item->code ?>" id="imagesForm">
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
	<input type="hidden" name="id" value="<?= $item->id ?>" />
	<input type="hidden" id="default_image_id" name="default_image_id" value="<?= htmlspecialchars((string)($defaultImageId ?? ''), ENT_QUOTES, 'UTF-8') ?>" />
	<input type="hidden" id="updateImage" name="updateImage" value="" />
	<input type="hidden" id="deleteImage" name="deleteImage" value="" />

	<section class="product-admin-images">
		<h3 class="product-admin-images__title">Product Images</h3>
		<p class="product-admin-images__hint">Use Set Default to choose the primary product image. Upload below or pick from the library.</p>

		<div class="product-admin-images__gallery">
<?php if (empty($images)) { ?>
			<p class="product-admin-images__empty">No images found for this product.</p>
<?php } else {
			foreach ($images as $image) {
				$thumbUrl = "/api/media/downloadMediaImage?height=100&width=100&code=" . urlencode($image->code);
				$isDefault = ($image->id == $defaultImageId);
?>
			<div class="product-admin-images__item<?= $isDefault ? ' product-admin-images__item--default' : '' ?>">
				<div class="product-admin-images__thumb">
					<img src="<?= htmlspecialchars($thumbUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($image->display_name ?? $image->name, ENT_QUOTES, 'UTF-8') ?>" />
				</div>
				<p class="product-admin-images__caption" title="<?= htmlspecialchars($image->display_name ?? $image->name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($image->display_name ?? $image->name) ?></p>
				<?php if ($isDefault) { ?>
				<span class="product-admin-images__badge">Default</span>
				<?php } else { ?>
				<button type="button" class="button btn-secondary product-admin-images__set-default" onclick="updateDefaultImage(<?= (int)$image->id ?>);">Set Default</button>
				<?php } ?>
				<button type="button" class="product-admin-images__delete" onclick="removeImageFromProduct(<?= (int)$image->id ?>);" title="Delete image" aria-label="Delete image">
					<img src="/img/icons/icon_tools_trash_active.svg" alt="" width="20" height="20" />
				</button>
			</div>
<?php
			}
		} ?>
		</div>
	</section>
</form>

<section class="product-admin-images__upload">
	<div class="product-admin-images__library">
		<h3 class="product-admin-images__upload-title">Select From Library</h3>
		<button type="button" class="button" onclick="initImageSelectWizard();">Open Image Library</button>
	</div>
	<form name="repoUpload" class="product-admin-images__upload-form" action="/_product/admin_images/<?= $item->code ?>" method="post" enctype="multipart/form-data">
		<h3 class="product-admin-images__upload-title">Upload Product Image</h3>
		<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
		<input type="hidden" name="repository_id" value="<?= $repository->id ?>" />
		<input type="file" name="uploadFile" accept="image/*" />
		<input type="submit" name="btn_submit" class="button" value="Upload" />
	</form>
</section>
<?php } else { ?>
<section class="product-admin-images">
	<h3 class="product-admin-images__upload-title">Upload Product Image</h3>
	<p class="register-admin-account-repository-error">Repository not found. (please create an S3, Local, Google or Dropbox repository to upload images for this product)</p>
</section>
<?php } ?>
