<script language="Javascript" src="/js/product.js" defer></script>
<script language="Javascript">
	// CSRF Token for API requests
	var csrfToken = '<?=$GLOBALS['_SESSION_']->getCSRFToken()?>';
	
	// Counter for newly added images
	var new_image_count = 0;

	/** @function initImageSelectWizard
	 * Popup New Image Selection Window to user can add existing images to product
	 */
    function initImageSelectWizard() {
        var imageSelectUrl = "/_media/image_select";
        console.log("Opening image select window with URL: " + imageSelectUrl);
        
        // Test if the URL is accessible by trying to fetch it first
        fetch(imageSelectUrl)
            .then(response => {
                if (response.ok) {
                    console.log("URL is accessible, opening popup");
                    childWindow = open(imageSelectUrl, "imageselect", 'resizable=no,width=500,height=500');
                    if (childWindow.opener == null) childWindow.opener = self;
                } else {
                    console.error("URL returned status: " + response.status);
                    alert("Error: Image select page returned status " + response.status);
                }
            })
            .catch(error => {
                console.error("Error accessing image select URL: " + error);
                alert("Error accessing image select page: " + error.message);
            });
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
        //document.getElementById('productEdit').submit();
    }
</script>
<style>
	.image-item {
		width: 100px;
		height: 100px;
		background-size: cover;
		background-position: center;
		border: 1px solid #ccc;
		margin: 5px;
		display: inline-block;
		cursor: pointer;
	}
	.image-item.highlighted {
		border-color: blue;
	}
</style>
<link href="/css/upload.css" type="text/css" rel="stylesheet">

<?=$page->showAdminPageInfo()?>

<div id="page_top_nav" style="margin-bottom: 20px;">
	<a href="/_spectros/admin_product/<?= $item->code ?>" class="button">Details</a>
	<a href="/_product/admin_product_prices/<?= $item->code ?>" class="button">Prices</a>
	<a href="/_product/admin_product_vendors/<?= $item->code ?>" class="button">Vendors</a>
	<a href="/_product/admin_images/<?= $item->code ?>" class="button" disabled>Images</a>
	<a href="/_product/admin_product_tags/<?= $item->code ?>" class="button">Tags</a>
	<a href="/_product/admin_product_parts/<?= $item->code ?>" class="button">Parts</a>
</div>

<?php if ($repository->id) { ?>
	<!-- File Upload Form -->
    <form name="repoUpload" action="/_product/admin_images/<?= $item->code ?>" method="post" enctype="multipart/form-data">
        <div class="container">
            <h3 class="label">Upload Product Image for this device</h3>
            <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
            <input type="hidden" name="repository_id" value="<?= $repository->id ?>" />
            <input type="file" name="uploadFile" />
            <input type="submit" name="btn_submit" class="button" value="Upload" />
        </div>
    </form>
	<div class="container">
		<h3 class="label">Select Image from Repository</h3>
		<button class="button" onclick="initImageSelectWizard();">Select Image</button>
	</div>

	<!-- Display Existing Images, Allow user to select a new default -->
	<form method="post" action="/_product/admin_images" id="imagesForm">
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
	<input type="hidden" name="id" value="<?= $item->id ?>" />
	<input type="hidden" id="default_image_id" name="default_image_id" value="<?= ($defaultImage = $item->getDefaultStorageImage()) ? $defaultImage->name : '' ?>" />
	<input type="hidden" id="updateImage" name="updateImage" value="" />

	<div class="container">
		<h3 class="label">Current Images</h3>
<?php 	if (isset($images) && count($images) > 0) { ?>
		<div id="image_box" class="image-list">
			<?php foreach ($images as $image) { ?>
				<div id="ItemImageDiv_<?= $image->id ?>" onclick="highlightImage(<?= $image->id ?>);">
					<div class="image-item" style="background-image: url('/api/media/downloadMediaImage?height=100&width=100&code=<?= $image->code ?>');"></div>
					<span class="image-code"><?= $image->display_name ?></span>
					<?php if ($image->id == $defaultImageId) { ?>
						<span class="default-image">Default</span>
					<?php } else { ?>
					<button class="button" onclick="updateDefaultImage(<?= $image->id ?>);">Set as Default</button>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
<?php 	} else { ?>
	<p>No images found for this product.</p>
<?php } ?>
	</div>
	</form>
<?php } else {
?>
	<!-- Repository not found, display message -->
    <div class="container">
        <h3 class="label">Upload Product Image for this device</h3>
        <p style="color: red;">Repository not found. (please create an S3, Local, Google or Dropbox repository to upload images for this product)</p>
    </div>
<?php
}
?>

