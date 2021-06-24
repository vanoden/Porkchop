<?php
    $page = new \Site\Page();
    $page->requireRole('storage manager');

    $repositoryList = new \Storage\RepositoryList();
    $repositories = $repositoryList->find();
