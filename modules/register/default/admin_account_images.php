<link href="/css/upload.css" type="text/css" rel="stylesheet">

<script type="text/javascript">
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
    document.getElementById('btn_submit').click();
  }
</script>

<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php $activeTab = 'images'; ?>

<div class="tabs">
    <a href="/_register/admin_account?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='login'?'active':'' ?>">Login / Registration</a>
    <a href="/_register/admin_account_contacts?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='contacts'?'active':'' ?>">Methods of Contact</a>
    <a href="/_register/admin_account_password?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='password'?'active':'' ?>">Change Password</a>
    <a href="/_register/admin_account_roles?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='roles'?'active':'' ?>">Assigned Roles</a>
    <a href="/_register/admin_account_auth_failures?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='auth_failures'?'active':'' ?>">Recent Auth Failures</a>
    <a href="/_register/admin_account_terms?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='terms'?'active':'' ?>">Terms of Use History</a>
    <a href="/_register/admin_account_locations?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='locations'?'active':'' ?>">Locations</a>
    <a href="/_register/admin_account_images?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='images'?'active':'' ?>">User Images</a>
    <a href="/_register/admin_account_backup_codes?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='backup_codes'?'active':'' ?>">Backup Codes</a>
    <a href="/_register/admin_account_search_tags?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='search_tags'?'active':'' ?>">Search Tags</a>
</div>

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account_images?customer_id=<?= $customer_id ?>" method="POST">

  <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
  <input type="hidden" name="target" value="<?= $target ?>" />
  <input type="hidden" name="customer_id" value="<?= $customer_id ?>" />
  <input type="hidden" name="login" value="<?= $customer->code ?>" />

  <input type="hidden" name="deleteImage" id="deleteImage" value="" />
  <input type="hidden" id="default_image_id" name="default_image_id" value="" />
  <input type="hidden" id="updateImage" name="updateImage" value="" />

  <section id="form-message">
    <ul class="connectBorder infoText">
      <li>Make changes and click 'Apply' to complete.</li>
    </ul>
  </section>

  <!-- ============================================== -->
  <!-- USER IMAGES -->
  <!-- ============================================== -->
  <div class="input-horiz" id="itemImages">
    <h3 class="label align-top">User Images</h3><br />
    <?php
    $defaultImageId = $customer->getMetadata('default_image');
    $hasImages = false;
    if ($defaultImageId) {
      $defaultImage = new \Storage\File($defaultImageId);
      if ($defaultImage->id) {
        $hasImages = true;
    ?>
        <div class="image-container">
          <h4>Current Default Image</h4>
          <img src="/_storage/downloadfile?file_id=<?= $defaultImageId ?>" class="register-admin-image" />
          <p><?= htmlspecialchars($defaultImage->name) ?></p>
        </div>
    <?php
      }
    }
    ?>
    <h2>Click to select your default user image</h2>
    <div class="images-container">
      <?php
      if (empty($customerImages)) {
        if (!$hasImages) {
      ?>
          <h4 class="no-images-found">No images found for this user.</h4>
        <?php
        }
      } else {
        if (!empty($customerImages)) { foreach ($customerImages as $image) {
          $hasImages = true;
        ?>
          <div class="image-item" id="ItemImageDiv_<?= $image->id ?>" onclick="highlightImage('<?= $image->id ?>'); updateDefaultImage('<?= $image->id ?>');">
            <img src="/_storage/downloadfile?file_id=<?= $image->id ?>" class="max-width-100px" />
            <p><?= htmlspecialchars($image->name) ?></p>
            <?php if ($defaultImageId == $image->id): ?>
              <span class="image-indicator">Default</span>
            <?php endif; ?>
          </div>
      <?php
        } }
      }
      ?>
    </div>
  </div>

  <?php if ($repository->id) { ?>
    <form name="repoUpload" action="/_register/admin_account_images?customer_id=<?= $customer->id ?>" method="post" enctype="multipart/form-data">
      <div class="container">
        <h3 class="label">Upload User Image for this account</h3>
        <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
        <input type="hidden" name="repository_id" value="<?= $repository->id ?>" />
        <input type="file" name="uploadFile" />
        <input type="submit" name="btn_submit" class="button" value="Upload" />
      </div>
    </form>
  <?php    } else {
  ?>
    <div class="container">
      <h3 class="label">Upload User Image for this account</h3>
      <p class="register-admin-account-repository-error">Repository not found. (please create an S3, Local, Google or Dropbox repository to upload images for this user)</p>
    </div>
  <?php
  }
  ?>

  <!-- entire page button submit -->
  <div id="submit-button-container" class="tableBody min-tablet">
    <div class="tableRow button-bar">
      <input id="btn_submit" type="submit" name="method" class="button" value="Apply" />
    </div>
  </div>
</form>
