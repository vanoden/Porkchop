<html>
<head>
    <style>
        #image-container {
            width: 600px;
            height: 600px;
            overflow: auto;
        }
        .media-image-select {
            float: left;
            width: 110px;
            height: 110px;
            background-color: gray;
            display: block;
            overflow: hidden;
            margin: 5px;
            border: 1px solid #ccc;
            cursor: pointer;
        }
        .media-image-select:hover {
            border-color: blue;
        }
        .media-image-select img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .no-images {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
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
        } else { ?>
            <div class="no-images">
                <p>No images found in the repository.</p>
                <p>Please upload some images first.</p>
            </div>
        <?php } ?>
    </div>
</body>
</html>