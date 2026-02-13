<?= $page->showBreadcrumbs(); ?>
<?= $page->showTitle(); ?>
<?= $page->showMessages(); ?>

<section class="error-page" aria-labelledby="error-page-heading">
	<div class="error-page-content">
		<p class="error-page-lead">Sorry, but we can't find that page.</p>
		<p class="error-page-detail">The page you're looking for may have been moved, deleted, or the URL may be incorrect.</p>

		<nav class="error-page-actions" aria-label="Next steps">
			<h2 id="error-page-heading" class="error-page-actions-title">What would you like to do?</h2>
			<ul class="error-page-actions-list">
				<li><a href="/" class="error-page-action-link">Go to Home Page</a></li>
				<li><a href="javascript:history.back()" class="error-page-action-link">Go Back</a></li>
				<li><a href="/_site/search" class="error-page-action-link">Search the Site</a></li>
			</ul>
		</nav>

		<?php if (!empty($GLOBALS['_REQUEST_']->module) || !empty($GLOBALS['_REQUEST_']->view)) { ?>
		<p class="error-page-debug">Requested: <?php
			if (!empty($GLOBALS['_REQUEST_']->module)) echo "Module: ".htmlspecialchars($GLOBALS['_REQUEST_']->module)." ";
			if (!empty($GLOBALS['_REQUEST_']->view)) echo "View: ".htmlspecialchars($GLOBALS['_REQUEST_']->view)." ";
			if (!empty($GLOBALS['_REQUEST_']->index)) echo "Index: ".htmlspecialchars($GLOBALS['_REQUEST_']->index);
		?></p>
		<?php } ?>
	</div>
</section>
