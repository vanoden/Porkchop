<script language="Javascript">
    function initImageSelectWizard() {
        childWindow = open("http://<?= $_SERVER['HTTP_HOST'] ?>/api/storage/downloadFile/", "imageselect", 'resizable=no,width=500,height=500');
        if (childWindow.opener == null) childWindow.opener = self;
    }

    function endImageSelectWizard(code) {
        document.getElementById('new_image_code').value = code;
        document.getElementById('newImageBox').style.backgroundImage = '/api/storage/downloadFile/' + code;
    }

    function highlightImage(id) {

        // Remove highlight from all images
        var images = document.getElementsByClassName('image-item');
        for (var i = 0; i < images.length; i++) {
            images[i].classList.remove('highlighted');
        }
        // Add highlight to the clicked image
        document.getElementById('ItemImageDiv_' + id).classList.add('highlighted');
    }

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
</div>

<?php if ($repository->id) { ?>
    <form name="repoUpload" action="/_product/admin_images/<?= $item->code ?>" method="post" enctype="multipart/form-data">
        <div class="container">
            <h3 class="label">Upload Product Image for this device</h3>
            <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
            <input type="hidden" name="repository_id" value="<?= $repository->id ?>" />
            <input type="file" name="uploadFile" />
            <input type="submit" name="btn_submit" class="button" value="Upload" />
        </div>
    </form>
	<div id="newImageBox" style="width: 100px; height: 100px; background-size: cover; background-position: center; margin-top: 10px;"></div>
	<input type="hidden" id="new_image_code" name="new_image_code" value="" />
	<div class="container">
		<h3 class="label">Select Image from Repository</h3>
		<button class="button" onclick="initImageSelectWizard();">Select Image</button>
	</div>

	<form method="post" action="/_product/admin_images">
		<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
		<input type="hidden" name="id" value="<?= $item->id ?>" />
		<input type="hidden" id="default_image_id" name="default_image_id" value="<?= ($defaultImage = $item->getDefaultStorageImage()) ? $defaultImage->name : '' ?>" />
		<input type="hidden" id="updateImage" name="updateImage" value="" />

	<div class="container">
		<h3 class="label">Current Images</h3>
		<?php if (isset($images) && count($images) > 0) { ?>
			<div class="image-list">
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
			</div>
						</form>
<?php } else { ?>
	<p>No images found for this product.</p>
<?php } ?>
	</div>
<?php    } else {
?>
    <div class="container">
        <h3 class="label">Upload Product Image for this device</h3>
        <p style="color: red;">Repository not found. (please create an S3, Local, Google or Dropbox repository to upload images for this product)</p>
    </div>
<?php
}
?>

