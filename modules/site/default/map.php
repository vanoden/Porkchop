<?
	$pages = $pageList->find(array('module' => 'register'));
	print '<div class="sitemap_module">My Account</div>';
	foreach ($pages as $page) {
		if ($page->view == 'api') {
			continue;
		}
		else {
			print '<div class="sitemap_container">';
			print '  <a href="/_register/'.$page->view.'" class="sitemap_title">'.$page->view.'</a>';
			print '</div>';
		}
	}

	$pages = $pageList->find(array('module' => 'content'));
	print '<div class="sitemap_module">Site Content</div>';
	foreach ($pages as $page) {
		if ($page->view == 'api') {
			continue;
		}
		else {
			if (! isset($page->title)) $page->title = $page->index;
			print '<div class="sitemap_container">';
			print '  <a href="/_content/'.$page->index.'" class="sitemap_title">'.$page->title.'</a>';
			print '</div>';
		}
	}
?>
