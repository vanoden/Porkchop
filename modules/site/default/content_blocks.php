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

<div class="table site-content-blocks-table">
	<div class="tableHead site-content-blocks-table-head">
		<div class="tableCell site-content-blocks-table-cell">Target</div>
		<div class="tableCell site-content-blocks-table-cell">Name</div>
		<div class="tableCell site-content-blocks-table-cell">Actions</div>
	</div>
<?php	foreach ($messages as $message) { ?>
	<div class="tableRow site-content-blocks-table-row">
		<div class="tableCell site-content-blocks-table-cell"><?=$message->target?></a></div>
		<div class="tableCell site-content-blocks-table-cell"><?=$message->name?></div>
		<div class="tableCell site-content-blocks-table-cell">
			<a href="/_content/<?=$message->target?>">View</a>
<?php		if ($GLOBALS['_SESSION_']->customer->has_privilege('edit content messages')) { ?>
			<a href="/_site/content_block/<?=$message->target?>">Edit</a>
			<form method="post" action="/_site/content_block/<?=htmlspecialchars($message->target)?>" style="display:inline" onsubmit="return confirm('Delete this content block?');">
				<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
				<input type="hidden" name="id" value="<?=(int)$message->id?>">
				<button type="submit" name="deleteSubmit" value="Delete">Delete</button>
			</form>
<?php		} ?>
		</div>
	</div>
<?php	} ?>
</div>
