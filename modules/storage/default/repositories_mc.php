<?php
    $page = new \Site\Page();
    $page->requirePrivilege('manage storage repositories');

    $repositoryList = new \Storage\RepositoryList();
    $repositories = $repositoryList->find();
