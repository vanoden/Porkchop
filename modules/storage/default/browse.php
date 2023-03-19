
<?=$page->showBreadcrumbs()?>
<?=$page->showTitle()?>
<?=$page->showMessages()?>

        <table class="body">
            <tr>
                <th>Name</th>
                <th>Download</th>
                <th>Mime-Type</th>
                <th>Size (Bytes)</th>
                <th>Date Created</th>
                <th>Owner</th>
                <th>Endpoint</th>
                <th>Read Protect</th>
                <th>Write Protect</th>
            </tr>
            <?php
                if (is_array($directories)) {
                    foreach ($directories as $directory) { ?>
                <tr>
                    <td colspan=9><a href="/_storage/browse?code=<?=$repository->code?>&path=<?=$directory->path?>"><?=$directory->name()?>/</a></td>
                </tr>
                <?php  } 
                }
                if (is_array($files)) {
                      foreach ($files as $file) { ?>
                    <tr>
                        <td><a href="/_storage/file?id=<?=$file->id?>"><?=$file->name()?></a></td>
                        <td><a href="/_storage/downloadfile?id=<?=$file->id?>">Download</a></td>
                        <td>
                            <?=$file->mime_type?>
                        </td>
                        <td>
                            <?=$file->size?>
                        </td>
                        <td>
                            <?=$file->date_created?>
                        </td>
                        <td>
                            <?=$file->owner()->full_name()?>
                        </td>
                        <td>
                            <?=$file->endpoint?>
                        </td>
                        <td>
                            <?=$file->read_protect?>
                        </td>
                        <td>
                            <?=$file->write_protect?>
                        </td>
                    </tr>
                    <?php  } 
                        }
                        ?>
        </table>
        <?php	if ($repository->id) { ?>
            <form name="repoUpload" action="/_storage/file" method="post" enctype="multipart/form-data">
                <div class="container">
                    <span class="label">Upload File</span>
                    <input type="hidden" name="repository_id" value="<?=$repository->id?>" />
                    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
                    <input type="hidden" name="path" value="<?=$_REQUEST['path']?>" />
                    <input type="file" name="uploadFile" />
                    <input type="submit" name="btn_submit" class="button" value="Upload" />
                </div>
            </form>
            <?php	} ?>
