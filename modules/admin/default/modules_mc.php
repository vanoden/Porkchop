<?
	$page = new \Site\Page();
	$moduleList = new \Site\ModuleList();
	$modules = $moduleList->find();
	if ($moduleList->error()) $page->addError("Error scanning modules: ".$moduleList->error());
?>
