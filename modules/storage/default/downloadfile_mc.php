<?php
    $page = new \Site\Page();

    if ($_REQUEST['file_id']) $_REQUEST['id'] = $_REQUEST['file_id'];
    $file = new \Storage\File($_REQUEST['id']);
    if ($file->error) {
        $page->addError($file->error);
    } elseif ($file->id < 1) {
        $page->addError("File not found");
    } else {
        $file->download();
		$page->addError($file->error);
    }