<?= $page->showAdminPageInfo() ?>

<?php $activeTab = 'tags'; ?>
<?php require __DIR__ . '/admin_product_identity.php'; ?>
<?php require __DIR__ . '/admin_product_tabs.php'; ?>

<form id="adminProductTagsForm" method="post" action="/_product/admin_product_tags/<?= $item->code ?>">
<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
<input type="hidden" name="id" value="<?= $item->id ?>" />
<input type="hidden" id="removeTagId" name="removeTagId" value="" />
<input type="hidden" id="removeSearchTagId" name="removeSearchTagId" value="" />

<?php
	$searchTagsTitle = 'Product Search Tags';
	$searchTagRows = $productSearchTags ?? [];
	$searchTagsFormId = 'adminProductTagsForm';
	$searchTagsDefaultCategory = 'product_tag';
	$searchTagsCategoryPlaceholder = 'gas';
	$searchTagsValuePlaceholder = 'sulfuryl fluoride';
	require BASE . '/modules/site/default/search_tags_editor.php';
?>
</form>
