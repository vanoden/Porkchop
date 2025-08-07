<?=$page->showAdminPageInfo()?>
<a class="button" href="/_storage/repository">New Repository</a>
<div class="tableBody">
<div class="tableRowHeader">
	<div class="tableCell">Code</div>
	<div class="tableCell">Name</div>
	<div class="tableCell">Type</div>
	<div class="tableCell">Status</div>
</div>
<?php	 foreach ($repositories as $repository) { ?>
<div class="tableRow">
	<div class="tableCell"><a href="/_storage/repository?code=<?=$repository->code?>"><?=$repository->code?></a></div>
	<div class="tableCell"><?=$repository->name?></div>
	<div class="tableCell"><?=$repository->type?></div>
	<div class="tableCell"><?=$repository->status?></div>
</div>
<?php	 } ?>
</div>