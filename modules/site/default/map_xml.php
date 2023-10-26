<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php
	$modules = $moduleList->find();
	foreach ($modules as $module) {
		$pages = $pageList->find(array('module' => $module->name(),'sitemap' => 1));
		if (count($pages)) {
			foreach ($pages as $page) {
				if ($module->name() == 'content' && $page->view() == 'index') {
?>
<sitemap>
	<loc><?=$site->url()?>/<?=$page->index?></loc>
</sitemap>
<?php			} else {
?>
<sitemap>
	<loc><?=$site->url()?>/_<?=$module->name()?>/<?=$page->view?></loc>
</sitemap>
<?php
				}
			}
		}
	}
?>
</sitemapindex>
<?php	exit; ?>
