<script type="text/javascript">
  function updateDefaultImage(imageId) {
    var form = document.getElementById('admin-account-form');
    if (!form) return;
    document.getElementById('default_image_id').value = imageId;
    document.getElementById('updateImage').value = 'true';
    form.submit();
  }
</script>

<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php
$activeTab = 'images';
require __DIR__ . '/admin_account_tabs.php';
?>

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account_images?customer_id=<?= $customer_id ?>" method="POST">

  <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
  <input type="hidden" name="target" value="<?= $target ?>" />
  <input type="hidden" name="customer_id" value="<?= $customer_id ?>" />
  <input type="hidden" name="login" value="<?= $customer->code ?>" />

  <input type="hidden" name="deleteImage" id="deleteImage" value="" />
  <input type="hidden" id="default_image_id" name="default_image_id" value="" />
  <input type="hidden" id="updateImage" name="updateImage" value="" />

  <section class="register-admin-account-images">
    <h3 class="register-admin-account-images__title">User Images</h3>
    <p class="register-admin-account-images__hint">Use Set Default on an image to make it the profile photo. Upload below to add a new image.</p>

    <?php $defaultImageId = $customer->getMetadata('default_image'); ?>

    <div class="register-admin-account-images__gallery">
<?php if (empty($customerImages)) { ?>
      <p class="register-admin-account-images__empty">No images found for this user.</p>
<?php } else {
      foreach ($customerImages as $image) {
        $isDefault = ($defaultImageId == $image->id);
?>
      <div class="register-admin-account-images__item<?= $isDefault ? ' register-admin-account-images__item--default' : '' ?>">
        <div class="register-admin-account-images__thumb">
          <img src="/_storage/downloadfile?file_id=<?= $image->id ?>" alt="<?= htmlspecialchars($image->name, ENT_QUOTES, 'UTF-8') ?>" />
        </div>
        <p class="register-admin-account-images__caption"><?= htmlspecialchars($image->name) ?></p>
        <?php if ($isDefault) { ?>
        <span class="register-admin-account-images__badge">Default</span>
        <?php } else { ?>
        <button type="button" class="button btn-secondary register-admin-account-images__set-default" onclick="updateDefaultImage('<?= $image->id ?>');">Set Default</button>
        <?php } ?>
      </div>
<?php }
    } ?>
    </div>
  </section>
</form>

  <section class="register-admin-account-images__upload">
<?php if (!empty($repository) && $repository->id) { ?>
    <form name="repoUpload" class="register-admin-account-images__upload-form" action="/_register/admin_account_images?customer_id=<?= $customer->id ?>" method="post" enctype="multipart/form-data">
      <h3 class="register-admin-account-images__upload-title">Upload User Image</h3>
      <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
      <input type="hidden" name="repository_id" value="<?= $repository->id ?>" />
      <input type="file" name="uploadFile" accept="image/*" />
      <input type="submit" name="btn_submit" class="button" value="Upload" />
    </form>
<?php } else { ?>
    <h3 class="register-admin-account-images__upload-title">Upload User Image</h3>
    <p class="register-admin-account-repository-error">Repository not found. (please create an S3, Local, Google or Dropbox repository to upload images for this user)</p>
<?php } ?>
  </section>

	</div><!-- .register-admin-account__content -->
</div><!-- .register-admin-account -->
