<?php
/**
 * Knowledge-center search tags list + combobox inputs for category/tag.
 *
 * Required:
 *   $searchTagsTitle (string)
 *   $searchTagRows (array of {searchTag, xrefId} objects, or SearchTag rows when $searchTagsRemoveKey = 'tag_id')
 *
 * Optional:
 *   $searchTagsSubtitle — default "(customer support knowledge center)"
 *   $searchTagsFormId — form id for removeSearchTagById()
 *   $searchTagsRemoveKey — "xref" (default) or "tag_id"
 *   $searchTagsCategoryPlaceholder, $searchTagsValuePlaceholder
 *   $searchTagsDefaultCategory — passed to search_tags_assets.php
 *   $uniqueTagsData
 *   $searchTagsSubmitInForm — false to omit Add button (parent form submits)
 */
$searchTagsSubtitle = $searchTagsSubtitle ?? '(customer support knowledge center)';
$searchTagsFormId = $searchTagsFormId ?? 'searchTagsForm';
$searchTagsRemoveKey = $searchTagsRemoveKey ?? 'xref';
$searchTagsCategoryPlaceholder = $searchTagsCategoryPlaceholder ?? 'category';
$searchTagsValuePlaceholder = $searchTagsValuePlaceholder ?? 'search tag';
$searchTagsSubmitInForm = $searchTagsSubmitInForm ?? true;
$searchTagRows = $searchTagRows ?? [];

require dirname(__FILE__) . '/search_tags_assets.php';

$searchTagCategoryOptions = json_decode($uniqueTagsData['categoriesJson'] ?? '[]', true) ?: [];
$searchTagValueOptions = json_decode($uniqueTagsData['tagsJson'] ?? '[]', true) ?: [];
$searchTagCategoryCount = count(array_filter(array_unique($searchTagCategoryOptions)));
$searchTagValueCount = count(array_filter(array_unique($searchTagValueOptions)));
?>
<section class="search-tags-editor">
	<h3 class="search-tags-editor__title"><?= htmlspecialchars($searchTagsTitle, ENT_QUOTES, 'UTF-8') ?></h3>
	<p class="search-tags-editor__subtitle"><?= htmlspecialchars($searchTagsSubtitle, ENT_QUOTES, 'UTF-8') ?></p>

	<div class="tableBody min-tablet search-tags-editor__list">
		<div class="tableRowHeader">
			<div class="tableCell width-33per">&nbsp;</div>
			<div class="tableCell width-33per">Category</div>
			<div class="tableCell width-33per">Search Tag</div>
		</div>
<?php if (!empty($searchTagRows)) {
	foreach ($searchTagRows as $row) {
		if (is_object($row) && isset($row->searchTag)) {
			$searchTag = $row->searchTag;
			$removeId = ($searchTagsRemoveKey === 'tag_id') ? ($searchTag->id ?? 0) : ($row->xrefId ?? 0);
		} else {
			$searchTag = $row;
			$removeId = ($searchTagsRemoveKey === 'tag_id') ? ($searchTag->id ?? 0) : ($searchTag->xrefId ?? 0);
		}
		if (empty($searchTag->id)) {
			continue;
		}
?>
		<div class="tableRow">
			<div class="tableCell">
				<input type="button" onclick="removeSearchTagById('<?= (int) $removeId ?>')" name="removeSearchTag" value="Remove" class="button" />
			</div>
			<div class="tableCell"><?= htmlspecialchars($searchTag->category ?? '', ENT_QUOTES, 'UTF-8') ?></div>
			<div class="tableCell"><?= htmlspecialchars($searchTag->value ?? '', ENT_QUOTES, 'UTF-8') ?></div>
		</div>
<?php }
} else { ?>
		<div class="tableRow">
			<div class="tableCell" colspan="3">No search tags assigned.</div>
		</div>
<?php } ?>

		<div class="tableRow search-tags-editor__add">
			<div class="tableCell">
				<div class="search-tags-editor__field">
					<label for="newSearchTagCategory">Category</label>
					<div class="search-tags-editor__combobox">
						<input type="text" name="newSearchTagCategory" id="newSearchTagCategory" class="value input" value="" placeholder="<?= htmlspecialchars($searchTagsCategoryPlaceholder, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" />
					</div>
					<p class="search-tags-editor__hint">Type or pick from <?= (int) $searchTagCategoryCount ?> existing categor<?= $searchTagCategoryCount === 1 ? 'y' : 'ies' ?>.</p>
				</div>
			</div>
			<div class="tableCell">
				<div class="search-tags-editor__field">
					<label for="newSearchTag">Search tag</label>
					<div class="search-tags-editor__combobox">
						<input type="text" name="newSearchTag" id="newSearchTag" class="value input" value="" placeholder="<?= htmlspecialchars($searchTagsValuePlaceholder, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off" />
					</div>
					<p class="search-tags-editor__hint">Type or pick from <?= (int) $searchTagValueCount ?> existing tags<?= $searchTagCategoryCount ? ' (filtered when category matches)' : '' ?>.</p>
				</div>
			</div>
<?php if ($searchTagsSubmitInForm) { ?>
			<div class="tableCell search-tags-editor__actions">
				<input type="submit" name="addSearchTag" value="Add Search Tag" class="button" />
			</div>
<?php } ?>
		</div>
	</div>
</section>
