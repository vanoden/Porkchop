<?php
    $page = new \Site\Page();

    $file = new \Storage\File($_REQUEST['id']);
    if ($file->error) {
        $page->addError($file->error);
    }
    elseif ($file->id < 1) {
        $page->addError("File not found");
    }
    else {
        $file->download();
        exit;
    }
?>
<div class="form_error"><?=$page->errorString()?></div>