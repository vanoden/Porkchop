<?
	$module = $_REQUEST['module'];
	$view = $_REQUEST['view'];
	$index = $_REQUEST['index'];

	$page = new \Site\Page();
	if ($page->get($module,$view,$index)) {
		$module = $page->module;
		$view = $page->view;
		$index = $page->index;
		if (! strlen($index)) $index = '[null]';

		$metadata = $page->metadata();
	}
?>
