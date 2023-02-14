<?=$page->showBreadCrumbs()?>
<?=$page->showMessages()?>
<form method="post" action="/_site/tou_versions">
<div class="table">
	<div class="tableHead">
		<div class="tableCell">Version</div>
		<div class="tableCell">Status</div>
		<div class="tableCell">Date Created</div>
		<div class="tableCell">Date Published</div>
		<div class="tableCell">Date Retracted</div>
		<div class="tableCell">Action</div>
	</div>
<?php	foreach ($termsOfUse as $tou) { ?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_site/tou_version?id=<?=$tou->id?>"><?=$version->number()?></a></div>
		<div class="tableCell"><?=$version->status?></div>
		<div class="tableCell"><?=$version->dateReleased()?></div>
		<div class="tableCell"><?=$version->datePublished()?></div>
		<div class="tableCell"><?=$version->dateRetracted()?></div>
		<div class="tableCell">
<?php	if ($version->status == 'NEW') {?>
		<input type="button" name="todo" value="Edit" />
		<input type="button" name="todo" value="Publish" />
<?php	} elseif ($version->status == '') { ?>
		<input type="button" name="todo" value="Retract" />
<?php	} ?>		
		</div>
	</div>
<?php	} ?>
</div>
<a href="/_site/tou_version" class="button">Add Version</a>