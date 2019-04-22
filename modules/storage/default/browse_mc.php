<?php
    $page = new \Site\Page();
    $page->requireRole('storage manager');

    $repository = new \Storage\Repository();
    $repository->get($_REQUEST['code']);
    if ($repository->error) {
        $page->addError($repository->error);
    }
    elseif(! $repository->id) {
        $page->addError("Repository not found");
    }
    else {
        $files = $repository->files();
    }
?>
