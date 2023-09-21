<?php
    $page = new \Site\Page();

    if ($_REQUEST['file_id']) $_REQUEST['id'] = $_REQUEST['file_id'];
    $file = new \Storage\File($_REQUEST['id']);

    if ($file->error()) {
        $page->addError($file->error());
		print_r($file->error());
    }
	elseif ($file->id < 1) {
        $page->addError("File not found");
		print_r("File not found");
		return 404;
    }
	else {
        $file->download();
		if ($file->error()) {
			$page->addError($file->error());
			print_r($file->error());
		}
		else exit(0);
    }
