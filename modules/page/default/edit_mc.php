<?
	$module = $REQUEST['module'];
	$view = $REQUEST['view'];

	$page = new \Site\Page(array('module' => $module, 'view' => $view));
?>
