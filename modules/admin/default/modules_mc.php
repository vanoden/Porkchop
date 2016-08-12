<?
	require_once(MODULES."/admin/_classes/admin.php");

	$_module = new PorkchopModule();
	$modules = $_module->find();
	if ($_module->error) $GLOBALS['_page']->error = "Error scanning modules: ".$_module->error;
?>
