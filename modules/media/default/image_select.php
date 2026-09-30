<html>
<head>
    <script language="javascript" src="/js/product.js"></script>
    <script language="javascript">
        function selectImage(code) {
            if (code) {
                window.opener.endImageSelectWizard(code);
                window.close();
            }
            return false;
        }
    </script>
</head>
<body>
    <div id="image-container">
        <?php
        # Loop Through and Display Images
        if (!empty($images) && count($images) > 0) {
            foreach ($images as $image) { ?>
                <a href="javascript:void(0)" onclick="return selectImage('<?= $image->code ?>');" class="media-image-select">
                    <img src="/api/media/downloadMediaImage?code=<?= $image->code ?>&height=100&width=100" class="media-image-select" alt="<?= htmlspecialchars($image->name) ?>" />
                </a>
            <?php   }
        } elseif (!empty($listError)) { ?>
            <div class="no-images">
                <p><?= htmlspecialchars($listError) ?></p>
            </div>
        <?php } else { ?>
            <div class="no-images">
                <p>No images found in this folder.</p>
                <p>Upload images first, then try again.</p>
            </div>
        <?php } ?>
    </div>
</body>
</html>
<?php	exit; ?>