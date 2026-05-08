<?= $page->showBreadcrumbs(); ?>
<?= $page->showTitle(); ?>
<?= $page->showMessages(); ?>

<section aria-labelledby="error-page-heading">
		<h2>Sorry, but we can't find that page.</h2>
		<p>The page you're looking for may have been moved, deleted, or the URL may be incorrect. What would you like to do?</p>
    <div class="button-group" role="group" aria-label="Error page actions">
      <a class="button button--primary"href="javascript:history.back()" class="error-page-action-link">Go Back</a>
      <a class="button button--secondary"href="/" class="error-page-action-link">Go to Home Page</a>
      <a class="button button--secondary"href="/_site/search" class="error-page-action-link">Search the Site</a>
    </div>

		<?php if (!empty($GLOBALS['_REQUEST_']->module) || !empty($GLOBALS['_REQUEST_']->view)) { ?>
		<p class="error-page-debug">Requested: <?php
			if (!empty($GLOBALS['_REQUEST_']->module)) echo "Module: ".htmlspecialchars($GLOBALS['_REQUEST_']->module)." ";
			if (!empty($GLOBALS['_REQUEST_']->view)) echo "View: ".htmlspecialchars($GLOBALS['_REQUEST_']->view)." ";
			if (!empty($GLOBALS['_REQUEST_']->index)) echo "Index: ".htmlspecialchars($GLOBALS['_REQUEST_']->index);
		?></p>
		<?php } ?>
</section>
