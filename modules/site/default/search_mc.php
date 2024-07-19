<?php
$page = new \Site\Page();
$search = new \Site\Search();
$page->requireAuth();

$definitionList = new \Site\Search\DefinitionList();
$definitionValues = $definitionList->getDefinitionList();

$results = array();
if (isset($_REQUEST['string']) && !empty($_REQUEST['string'])) {
    $_REQUEST['string'] = noXSS(trim($_REQUEST['string']));
    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
        $page->addError("Invalid Request");
    } else {
        $_REQUEST['definitions'] = isset($_REQUEST['definitions']) ? $_REQUEST['definitions'] : array();
        $results = $search->search($_REQUEST['string'], $_REQUEST['definitions']);
    }
}

// show the adminstrator column in the search results
$canAdministor = false;
$siteSearchDefinition = new Site\Search\Definition();
foreach ($results as $result) if ($siteSearchDefinition->ifPrivilege($result->admin_privilege)) $canAdministor = true;