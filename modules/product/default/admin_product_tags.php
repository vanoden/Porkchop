<!-- Autocomplete CSS and JS -->
<link href="/css/autocomplete.css" type="text/css" rel="stylesheet">
<script language="JavaScript" src="/js/autocomplete.js"></script>
<script language="JavaScript">
  // define existing categories and tags for autocomplete
  var existingCategories = <?= $uniqueTagsData['categoriesJson'] ?>;
  var existingTags = <?= $uniqueTagsData['tagsJson'] ?>;
  
  // remove a search tag by id
  function removeSearchTagById(id) {
    document.getElementById('removeSearchTagId').value = id;
    document.getElementById('adminProductTagsForm').submit();
  }

  // When adding a tag with empty category, pass product_tag from the UI
  document.getElementById('adminProductTagsForm').addEventListener('submit', function(ev) {
    var catInput = document.getElementById('newSearchTagCategory');
    var tagInput = document.getElementById('newSearchTag');
    if (tagInput && tagInput.value && catInput && !catInput.value.trim()) {
      catInput.value = 'product_tag';
    }
  });
</script>

<?= $page->showAdminPageInfo() ?>

<?php $activeTab = 'tags'; ?>
<?php
    $__defImg = $item->getDefaultStorageImage();
    if ($__defImg && $__defImg->id) {
        $thumb = "/api/media/downloadMediaImage?height=50&width=50&code=".$__defImg->code;
        $title = htmlspecialchars($item->getMetadata('name') ?: $item->name ?: $item->code);
    }
?>
<div class="product-container">
    <img src="<?=$thumb?>" alt="Default" class="product-thumb" />
    <div class="product-title"><?=$title?></div>
</div>
<?php
?>
<div class="tabs">
    <a href="/_product/admin_product/<?= $item->code ?>" class="tab <?= $activeTab==='details'?'active':'' ?>">Details</a>
    <a href="/_product/admin_product_prices/<?= $item->code ?>" class="tab <?= $activeTab==='prices'?'active':'' ?>">Prices</a>
    <a href="/_product/admin_product_vendors/<?= $item->code ?>" class="tab <?= $activeTab==='vendors'?'active':'' ?>">Vendors</a>
    <a href="/_product/admin_images/<?= $item->code ?>" class="tab <?= $activeTab==='images'?'active':'' ?>">Images</a>
    <a href="/_product/admin_product_tags/<?= $item->code ?>" class="tab <?= $activeTab==='tags'?'active':'' ?>">Tags</a>
    <a href="/_product/admin_product_parts/<?= $item->code ?>" class="tab <?= $activeTab==='parts'?'active':'' ?>">Parts</a>
    <a href="/_product/admin_product_metadata/<?= $item->code ?>" class="tab <?= $activeTab==='metadata'?'active':'' ?>">Metadata</a>
    <a href="/_product/audit_log/<?= $item->code ?>" class="tab <?= $activeTab==='audit'?'active':'' ?>">Audit Log</a>
</div>

<form id="adminProductTagsForm" method="post" action="/_product/admin_product_tags/<?= $item->code ?>">
<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
<input type="hidden" name="id" value="<?= $item->id ?>" />
<input type="hidden" id="removeTagId" name="removeTagId" value="" />
<input type="hidden" id="removeSearchTagId" name="removeSearchTagId" value="" />

<h3 class="text-inline">Product Search Tags</h3>
<h4 class="text-inline">(customer support knowledge center)</h4>
<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell tableCell-width-33">Category</div>
		<div class="tableCell tableCell-width-33">Search Tag</div>
		<div class="tableCell tableCell-width-33">&nbsp;</div>
	</div>
<?php
	foreach ($productSearchTags as $row) {
		$searchTag = $row->searchTag;
		$xrefId = $row->xrefId;
?>
	<div class="tableRow">
		<div class="tableCell">
			<?= htmlspecialchars($searchTag->category ?: 'product_tag') ?>
		</div>
		<div class="tableCell">
			<?= htmlspecialchars($searchTag->value) ?>
		</div>
		<div class="tableCell">
			<img src="/img/icons/icon_tools_trash_active.svg" onclick="removeSearchTagById('<?= (int)$xrefId ?>')" style="cursor: pointer; width: 20px; height: 20px;" alt="Remove" title="Remove" />
		</div>
	</div>

<?php
	}
?>
	<br />
	<div class="tableRow">
		<div class="tableCell">
			<label>Category:</label>
			<input type="text" class="autocomplete" name="newSearchTagCategory" id="newSearchTagCategory" value="" placeholder="gas" />
			<ul id="categoryAutocomplete" class="autocomplete-list"></ul>
		</div>
		<div class="tableCell">
			<label>New Search Tag:</label>
			<input type="text" class="autocomplete" name="newSearchTag" id="newSearchTag" value="" placeholder="sulfuryl fluoride" />
			<ul id="tagAutocomplete" class="autocomplete-list"></ul>
		</div>
	</div>
	<div><input type="submit" name="addSearchTag" value="Add Search Tag" class="button" /></div>
</div>
</form>
