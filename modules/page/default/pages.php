<style>
	.container {
		position: relative;
		display: block;
		height: 20px;
		width: 100%;
		clear: both;
		border: 1px solid gray;
		text-decoration: none;
		color: black;
	}
	.meta_container {
		float: left;
		margin-right: 10px;
	}
	span.column,
	span.column_wide {
		position: relative;
		display: block;
		width: 160px;
		float: left;
	}
	span.column_wide {
		width: 640px;
	}
	span.label {
		font-weight: bold;
	}
	span.value {
	}
</style>
<div class="container">
	<span class="column label">Module</span>
	<span class="column label">View</span>
	<span class="column_wide label">Metadata</span>
</div>
<?	foreach ($pages as $page) { ?>
<a class="container" href="/_page/edit?module=<?=$page->module?>&view=<?=$page->view?>">
	<span class="column value"><?=$page->module?></span>
	<span class="column value"><?=$page->view?></span>
<?	foreach ($page->metadata as $key => $value) { ?>
	<div class="meta_container">
		<span><?=$key?>:</span>
		<span><?=$value?></span>
	</div>
<?	} ?>
</a>
<?	} ?>
