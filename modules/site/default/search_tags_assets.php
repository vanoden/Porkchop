<?php
/**
 * Shared CSS/JS for knowledge-center search tag editors.
 * Include once per page before search_tags_editor.php.
 *
 * Optional: $uniqueTagsData, $searchTagsFormId, $searchTagsDefaultCategory
 */
if (!empty($GLOBALS['_search_tags_assets_loaded'])) {
	return;
}
$GLOBALS['_search_tags_assets_loaded'] = true;

if (empty($uniqueTagsData) || !is_array($uniqueTagsData)) {
	$searchTagList = new \Site\SearchTagList();
	$uniqueTagsData = $searchTagList->getUniqueCategoriesAndTagsJson();
}

$searchTagsFormId = $searchTagsFormId ?? 'searchTagsForm';
$searchTagsDefaultCategory = $searchTagsDefaultCategory ?? '';
?>
<link href="/css/autocomplete.css" type="text/css" rel="stylesheet">
<link href="/css/search-tags.css" type="text/css" rel="stylesheet">
<script src="/js/autocomplete.js"></script>
<script src="/js/search-tags.js"></script>
<script>
	window.searchTagsFormId = <?= json_encode($searchTagsFormId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
	window.searchTagsDefaultCategory = <?= json_encode($searchTagsDefaultCategory, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
	window.existingCategories = <?= $uniqueTagsData['categoriesJson'] ?? '[]' ?>;
	window.existingTags = <?= $uniqueTagsData['tagsJson'] ?? '[]' ?>;
	window.searchTagsByCategory = <?= $uniqueTagsData['tagsByCategoryJson'] ?? '{}' ?>;
	window.searchTagsAllTags = <?= $uniqueTagsData['tagsJson'] ?? '[]' ?>;

	function removeSearchTagById(id) {
		var formId = window.searchTagsFormId || 'searchTagsForm';
		var form = document.getElementById(formId);
		var removeField = document.getElementById('removeSearchTagId');
		if (!form || !removeField) return;
		removeField.value = id;
		form.submit();
	}

	document.addEventListener('DOMContentLoaded', function() {
		var form = document.getElementById(window.searchTagsFormId);
		if (!form || !window.searchTagsDefaultCategory) {
			return;
		}
		form.addEventListener('submit', function() {
			var catInput = document.getElementById('newSearchTagCategory');
			var tagInput = document.getElementById('newSearchTag');
			if (tagInput && tagInput.value && catInput && !catInput.value.trim()) {
				catInput.value = window.searchTagsDefaultCategory;
			}
		});
	});
</script>
