<?php
$page = new \Site\Page();
$search = new \Site\Search();

$definitionList = new \Site\Search\DefinitionList();
$definitionValues = $definitionList->getDefinitionList();




if (isset($_REQUEST['string']) && !empty($_REQUEST['string'])) {
    $_REQUEST['string'] = noXSS(trim($_REQUEST['string']));
    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
        $page->addError("Invalid Request");
    } else {
        $results = array();
        $results = $search->search($_REQUEST['string'], $_REQUEST['definitions']);
    }
}