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
<span class="title">Content Blocks</span>
<a href="/_site/content_block?method=new">New Block</a>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<div class="table">
	<div class="tableHead">
		<div class="tableCell">Target</div>
		<div class="tableCell">Name</div>
		<div class="tableCell">Actions</div>
	</div>
<?php	foreach ($messages as $message) { ?>
	<div class="tableRow">
		<div class="tableCell"><?=$message->target?></a></div>
		<div class="tableCell"><?=$message->name?></div>
		<div class="tableCell">
			<a href="/_content/<?=$message->target?>">View</a>
<?php		if ($GLOBALS['_SESSION_']->customer->has_privilege('edit content messages')) { ?>
			<a href="/_site/content_block/<?=$message->target?>">Edit</a>
<?php		} ?>
		</div>
	</div>
<?php	} ?>
</div>
