<script language="Javascript" src="/js/product.js" defer></script>
<style>
/* Enhanced Current Images Section Styling */
.current-images-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e3e3e3;
}

.image-count {
    background: #f8f9fa;
    color: #6c757d;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 500;
}

.image-item-container {
    position: relative;
    border: 2px solid #e3e3e3;
    border-radius: 8px;
    padding: 12px;
    background: #fff;
    transition: all 0.3s ease;
    cursor: pointer;
}

.image-item-container:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    transform: translateY(-2px);
}

.image-item-container.default-image-container {
    border-color: #28a745;
    background: linear-gradient(135deg, #f8fff9 0%, #ffffff 100%);
}

.image-item-container.default-image-container:hover {
    border-color: #1e7e34;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
}

.image-item {
    width: 150px;
    height: 150px;
    margin: 0 auto 12px;
    border-radius: 6px;
    background-size: cover;
    background-position: center;
    position: relative;
    border: 1px solid #dee2e6;
}

.default-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #28a745;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.image-info {
    text-align: center;
}

.image-code {
    font-size: 13px;
    font-weight: 500;
    color: #495057;
    margin-bottom: 8px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

.image-actions {
    margin-top: 8px;
}

.default-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #d4edda;
    color: #155724;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.button-small {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 4px;
    background: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.button-small:hover {
    background: #0056b3;
    transform: translateY(-1px);
}

.no-images-message {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.no-images-message i {
    font-size: 48px;
    color: #dee2e6;
    margin-bottom: 16px;
    display: block;
}

.no-images-message p {
    margin: 8px 0;
    font-size: 16px;
}

.text-muted {
    color: #6c757d;
    font-size: 14px;
}

/* 3 Column Grid Layout */
.image-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    max-width: 100%;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    .image-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
}

@media (max-width: 768px) {
    .current-images-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .image-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }
    
    .image-item {
        width: 120px;
        height: 120px;
    }
}

@media (max-width: 480px) {
    .image-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .image-item {
        width: 100px;
        height: 100px;
    }
}
</style>
<script language="Javascript">
	// CSRF Token for API requests
	var csrfToken = '<?=$GLOBALS['_SESSION_']->getCSRFToken()?>';
	
	// Counter for newly added images
	var new_image_count = 0;

	// Set background images from data attributes
	document.addEventListener('DOMContentLoaded', function() {
		var imageItems = document.querySelectorAll('.product-admin-images-background');
		imageItems.forEach(function(item) {
			var backgroundImage = item.getAttribute('data-background-image');
			if (backgroundImage) {
				item.style.backgroundImage = 'url(' + backgroundImage + ')';
			}
		});
	});

	/** @function initImageSelectWizard
	 * Popup New Image Selection Window to user can add existing images to product
	 */
    function initImageSelectWizard() {
		var repositoryCode = "<?=$repository->code?>";
		var path = "/spectros_product_image";
        var imageSelectUrl = "/_media/image_select?repository_code="+repositoryCode+"&path="+path;
        console.log("Opening image select window with URL: " + imageSelectUrl);
        
        // Open the image select popup window
        childWindow = open(imageSelectUrl, "imageselect", 'resizable=no,width=500,height=500,location=no,menubar=no,scrollbars=yes,status=no,toolbar=no');
        if (childWindow.opener == null) childWindow.opener = self;
    }

	/** @function endImageSelectWizard(code)
	 * Callback from Image Select Window
	 * Assign Selected Image to the Product via API and then
	 * to the Image Box.
	 * @param {string} code - The code of the selected image.
	 */
    function endImageSelectWizard(code) {
		if (!code) {
			console.error("No image code provided");
			return;
		}

		console.log("Adding image with code: " + code);

		// Check if Item object is available
		if (typeof Item === 'undefined') {
			console.error("Item object is not defined - cannot proceed");
			return;
		}

		// Assign new image to product via API call
		var product = Object.create(Item);
		product.get('<?=$item->code?>');
		
		// Check if product loaded successfully
		if (product.error) {
			console.error("Error loading product: " + product.error);
			return;
		}

		// Add image to product
		if (product.addImage(code)) {
			console.log("Image added successfully to product");
			
			// Close the popup window
			window.close();
		} else {
			console.error("Error adding image: " + product.error);
		}
    }

	/** @function highlightImage(id)
	 * Highlight Selected Image
	 * @param {string} id - The ID of the image to highlight.
	 */
    function highlightImage(id) {
        // Remove highlight from all images
        var images = document.getElementsByClassName('image-item');
        for (var i = 0; i < images.length; i++) {
            images[i].classList.remove('highlighted');
        }
        // Add highlight to the clicked image
        var targetElement = document.getElementById('ItemImageDiv_' + id);
        if (targetElement) {
            var imageItem = targetElement.querySelector('.image-item');
            if (imageItem) {
                imageItem.classList.add('highlighted');
            }
        }
    }

	/** @function updateDefaultImage(imageId)
	 * Set Selected Image as Default
	 * @param {string} imageId - The ID of the image to set as default.
	 */
    function updateDefaultImage(imageId) {
        document.getElementById('default_image_id').value = imageId;
        document.getElementById('updateImage').value = 'true';
        var form = document.getElementById('imagesForm');
        if (form) form.submit();
    }

	/** @function removeDefaultImage()
	 * Remove the current default image
	 */
    function removeDefaultImage() {
        document.getElementById('default_image_id').value = '';
        document.getElementById('updateImage').value = 'true';
        var form = document.getElementById('imagesForm');
        if (form) form.submit();
    }

	/** @function removeImageFromProduct(imageId)
	 * Delete/Remove an image from the product
	 * @param {string} imageId - The ID of the image to delete.
	 */
    function removeImageFromProduct(imageId) {
        if (confirm('Are you sure you want to remove this image from the product? This will disassociate the image but not delete it from the repository.')) {
            document.getElementById('deleteImage').value = imageId;
            var form = document.getElementById('imagesForm');
            if (form) form.submit();
        }
    }
</script>


<?= $page->showAdminPageInfo() ?>

<?php $activeTab = 'images'; ?>
<?php
    // Small default image thumb + title above tabs
    $__defImg = $item->getDefaultStorageImage();
    if ($__defImg && $__defImg->id) {
        $thumb = "/api/media/downloadMediaImage?height=50&width=50&code=".$__defImg->code;
        $title = htmlspecialchars($item->getMetadata('name') ?: $item->name ?: $item->code);
    }
?>
<div class="product-container">
    <img src="<?=$thumb?>" alt="Default" class="product-thumb" />
    <div class="product-title"><?=$title?></div>
</div>
<?php
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
    // Show current default image below breadcrumbs if available
    $defaultImage = $item->getDefaultStorageImage();
    if ($defaultImage && $defaultImage->id) {
        $thumbUrl = "/api/media/downloadMediaImage?height=150&width=150&code=" . $defaultImage->code;
?>
    <div class="container container-flex-center">
        <img src="<?= $thumbUrl ?>" alt="Default image for <?= htmlspecialchars($item->code) ?>" class="img-default-thumb" />
        <div>
            <div class="label">Current Default Image</div>
            <div><?= htmlspecialchars($defaultImage->display_name ?? $defaultImage->name) ?></div>
            <div style="margin-top: 8px;">
                <button type="button" class="button button-small" onclick="removeDefaultImage();" style="background: #dc3545;">
                    <i class="fa fa-times-circle"></i> Remove as Default
                </button>
            </div>
        </div>
    </div>
<?php } ?>

<?php if ($repository->id) { ?>
	<!-- File Upload Form -->
    <div class="container container-block">
        <form name="repoUpload" action="/_product/admin_images/<?= $item->code ?>" method="post" enctype="multipart/form-data">
            <h3 class="label">Upload Product Image</h3>
            <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
            <input type="hidden" name="repository_id" value="<?= $repository->id ?>" />
            <input type="file" name="uploadFile" />
            <input type="submit" name="btn_submit" class="button" value="Upload" />
        </form>
        <div>
            <h3 class="label">Select From Library</h3>
            <button class="button" onclick="initImageSelectWizard();">Open Image Library</button>
        </div>
    </div>

	<!-- Display Existing Images, Allow user to select a new default -->
	<form method="post" action="/_product/admin_images/<?= $item->code ?>" id="imagesForm">
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
	<input type="hidden" name="id" value="<?= $item->id ?>" />
	<input type="hidden" id="default_image_id" name="default_image_id" value="<?= ($defaultImage = $item->getDefaultStorageImage()) ? $defaultImage->name : '' ?>" />
	<input type="hidden" id="updateImage" name="updateImage" value="" />
	<input type="hidden" id="deleteImage" name="deleteImage" value="" />

	<div class="container">
		<div class="current-images-header">
			<h3 class="label">Current Images</h3>
			<?php if (isset($images) && count($images) > 0) { ?>
				<span class="image-count"><?= count($images) ?> image<?= count($images) !== 1 ? 's' : '' ?></span>
			<?php } ?>
		</div>
		
<?php 	if (isset($images) && count($images) > 0) { ?>
        <div id="image_box" class="image-list image-grid">
            <?php foreach ($images as $image) { 
                $thumb = "/api/media/downloadMediaImage?height=150&width=150&code=".$image->code;
                $isDefault = ($image->id == $defaultImageId);
            ?>
                <div id="ItemImageDiv_<?= $image->id ?>" onclick="highlightImage(<?= $image->id ?>);" class="image-item-container <?= $isDefault ? 'default-image-container' : '' ?>">
                    <div class="image-item product-admin-images-background" data-background-image="<?= $thumb ?>">
                        <?php if ($isDefault) { ?>
                            <div class="default-badge">
                                <i class="fa fa-star"></i>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="image-info">
                        <div class="image-code" title="<?= htmlspecialchars($image->display_name) ?>"><?= htmlspecialchars($image->display_name) ?></div>
                        <div class="image-actions">
                            <?php if ($isDefault) { ?>
                                <span class="default-indicator">
                                    <i class="fa fa-check-circle"></i> Default
                                </span>
                            <?php } else { ?>
                                <button type="button" class="button button-small" onclick="updateDefaultImage(<?= $image->id ?>);">
                                    <i class="fa fa-star-o"></i> Set as Default
                                </button>
                            <?php } ?>
                            <button type="button" class="button button-small" onclick="removeImageFromProduct(<?= $image->id ?>); event.stopPropagation();" style="background: #dc3545; margin-left: 8px;">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
<?php 	} else { ?>
		<div class="no-images-message">
			<i class="fa fa-image"></i>
			<p>No images found for this product.</p>
			<p class="text-muted">Upload images using the form above or select from the image library.</p>
		</div>
<?php } ?>
	</div>
	</form>
<?php } else {
?>
	<!-- Repository not found, display message -->
    <div class="container">
        <h3 class="label">Upload Product Image for this device</h3>
        <p class="text-color-red">Repository not found. (please create an S3, Local, Google or Dropbox repository to upload images for this product)</p>
    </div>
<?php
}
?>

