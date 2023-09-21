<?php
	$page->showAdminPageInfo();
?>
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Name</div>
		<div class="tableCell">Description</div>
		<div class="tableCell">Active Version</div>
	</div>
<?php	foreach ($termsOfUse as $tou) { ?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_site/term_of_use?id=<?=$tou->id?>"><?=$tou->name?></a></div>
		<div class="tableCell"><?=$tou->description?></div>
		<div class="tableCell"><?=$tou->latestVersion()->id ?: 'none'?></div>
	</div>
<?php	} ?>
</div>
<a href="/_site/term_of_use" class="button">Add Terms of Use</a>
