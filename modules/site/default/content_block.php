<?php /* TinyMCE: If you see "invalid-origin" or read-only editors, add this site's domain (e.g. https://yoursite.com) in Tiny Cloud dashboard under Domain registration. Use HTTPS in production. */ ?>
<script src="https://cdn.tiny.cloud/1/owxjg74mr7ujxhw9soo7iquo7iul2mclregqovcp7ophazmn/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
	tinymce.init({
		selector: '#content',
        plugins: 'code',
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | code'
	});

  	// remove a search tag by id
	function removeSearchTagById(id) {
		document.getElementById('removeSearchTagId').value = id;
		document.getElementById('contentBlockEdit').submit();
	}
</script>

<!-- Autocomplete CSS and JS -->
<link href="/css/autocomplete.css" type="text/css" rel="stylesheet">
<script language="JavaScript" src="/js/autocomplete.js"></script>
<script language="JavaScript">
	// define existing categories and tags for autocomplete
	var existingCategories = <?= $uniqueTagsData['categoriesJson'] ?>;
	var existingTags = <?= $uniqueTagsData['tagsJson'] ?>;
</script>

<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<form id="contentBlockEdit" name="contentBlockEdit" method="post" action="/_site/content_block/<?= $message->target ?>">

	<input type="hidden" name="id" id="id" value="<?= $message->id ?>" />
	<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
	<input type="hidden" id="removeSearchTagId" name="removeSearchTagId" value="" />

	<div class="body">
		<div class="input-horiz" id="itemName">
			<span class="label">Name</span>
			<input type="text" class="value input width-250px" name="name" id="name" value="<?= htmlspecialchars($message->name) ?>" />
		</div>
		<div class="input-horiz" id="itemTarget">
			<span class="label">Target</span>
			<input type="text" class="value input width-250px" name="target" id="target" value="<?= htmlspecialchars($message->target) ?>" />
		</div>
		<div class="input-horiz" id="itemContent">
			<span class="label align-top">Content</span>
			<textarea class="value input width-250px" name="content" id="content"><?= htmlspecialchars($message->content) ?></textarea>
		</div>

		<br/><br/>
		<h3 class="text-inline">Content Message Search Tags</h3>
		<h4 class="text-inline">(customer support knowledge center)</h4>
		<div class="tableBody min-tablet">
			<div class="tableRowHeader">
				<div class="tableCell tableCell-width-33">&nbsp;</div>
				<div class="tableCell tableCell-width-33">Category</div>
				<div class="tableCell tableCell-width-33">Search Tag</div>
			</div>
			<?php
			foreach ($registerCustomerSearchTags as $row) {
				$searchTag = $row->searchTag;
				$xrefId = $row->xrefId;
			?>
				<div class="tableRow">
					<div class="tableCell">
						<input type="button" onclick="removeSearchTagById('<?= (int)$xrefId ?>')" name="removeSearchTag" value="Remove" class="button" />
					</div>
					<div class="tableCell">
						<?= htmlspecialchars($searchTag->category) ?>
					</div>
					<div class="tableCell">
						<?= htmlspecialchars($searchTag->value) ?>
					</div>
				</div>
			<?php
			}
			?>
			<br/>
			<div class="tableRow">
					<div class="tableCell">
						<label>Category:</label>
						<input type="text" class="autocomplete" name="newSearchTagCategory" id="newSearchTagCategory" value="" placeholder="content" />
						<ul id="categoryAutocomplete" class="autocomplete-list"></ul>
					</div>
					<div class="tableCell">
						<label>New Search Tag:</label>
						<input type="text" class="autocomplete" name="newSearchTag" id="newSearchTag" value="" placeholder="about us" />
						<ul id="tagAutocomplete" class="autocomplete-list"></ul>
					</div>
				</div>
				<div><input type="submit" name="addSearchTag" value="Add Search Tag" class="button" /></div>
		</div>

		<div class="editSubmit button-bar floating">
			<input type="submit" class="button" value="Update" name="updateSubmit" id="updateSubmit" />
		</div>
	</div>
</form>
