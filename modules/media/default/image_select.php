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
        }
        .media-image-select img {
            width: 100px;
        }
    </style>
    <script language="javascript">
        function selectImage(code) {
            window.opener.endImageSelectWizard(code);
            window.close();
            return false;
        }
    </script>
</head>
<body>
    <div id="image-container">
        <?php
        # Loop Through and Display Images
        foreach ($images as $image) { ?>
            <a href="javascript:void(0)" onclick="return selectImage('<?= $image->code ?>');" class="media-image-select">
                <img src="/api/media/downloadMediaFile?code=<?= $image->code ?>" class="media-image-select" />
            </a>
        <?php   }
        ?>
    </div>
</body>
</html>