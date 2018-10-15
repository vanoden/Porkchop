<?
	$module = $_REQUEST['module'];
	$view = $_REQUEST['view'];
	$index = $_REQUEST['index'];

	$page = new \Site\Page(array('module' => $module, 'view' => $view, 'index' => $index));

	$metadata = $page->metadata();
?>
