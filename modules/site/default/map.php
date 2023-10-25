<?php
	$modules = $moduleList->find();
	foreach ($modules as $module) {
		$pages = $pageList->find(array('module' => $module->name(),'sitemap' => 1));
		if (count($pages)) {
?>
<div class="sitemap_module"><?=ucwords($module->name())?></div>
<?php		foreach ($pages as $page) {
				if ($module->name() == 'content' && $page->view() == 'index') {
?>
<div class="sitemap_container">
	<a href="/<?=$page->index?>" class="sitemap_title"><?=$page->name()?></a>
</div>
<?php			} else {
?>
<div class="sitemap_container">
	<a href="/_register/<?=$page->view?>" class="sitemap_title"><?=$page->name()?></a>
</div>
<?php
				}
			}
		}
	}
?>
