<style>
	.table {
		display: table;
		width: 756px;
	}
	.tableHead {
		display: table-row;
		font-weight: bold;
		text-align: center;
	}
	.tableRow {
		display: table-row;
	}
	.tableCell {
		display: table-cell;
		padding: 3px 10px;
		border: 1px solid #999999;
	}
</style>
<span class="title">Site Pages</span>
<div class="table">
	<div class="tableHead">
		<div class="tableCell">Module</div>
		<div class="tableCell">View</div>
		<div class="tableCell">Index</div>
		<div class="tableCell">Template</div>
		<div class="tableCell">Metadata</div>
	</div>
<?php	foreach ($pages as $page) {
		$metadata = $page->allMetadata(); ?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_site/page?module=<?=$page->module()?>&view=<?=$page->view()?>&index=<?=$page->index?>"><?=$page->module()?></a></div>
		<div class="tableCell"><?=$page->view?></div>
<?php		if (!empty($page->index) && ($GLOBALS['_SESSION_']->customer->has_privilege('edit content messages'))) { ?>
		<div class="tableCell"><a href="/_site/content_block/<?=$page->index?>"><?=$page->index?></a></div>
<?php		} elseif (!empty($page->index)) { ?>
		<div class="tableCell"><?=$page->index?></div>
<?php		} else { ?>
		<div class="tableCell">&nbsp;</div>
<?php		} ?>
		<div class="tableCell"><?=$page->template()?></div>
		<div class="tableCell">
			<span>
<?php	$first = true;
	foreach ($metadata as $data) {
		if (! $first) print ",";
		$first = false;
?>
	
			<?=$data->key?> = <?=$data->value?>
<?php	} ?>
			</span>
		</div>
		</a>
	</div>
<?php	} ?>
</div>
