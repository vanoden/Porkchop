<?=$page->showBreadCrumbs()?>
<?=$page->showMessages()?>
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
<script language="Javascript">
	function edit(id) {
		document.forms[0].id.value = id;
		document.forms[0].action = '/_site/tou_version';
		document.forms[0].submit();
		return true;
	}
	function publish(id) {
		document.forms[0].method.value = 'publish';
		document.forms[0].id.value = id;
		document.forms[0].submit();
		return true;
	}
	function retract(id) {
		document.forms[0].method.value = 'retract';
		document.forms[0].id.value = id;
		document.forms[0].submit();
		return true;
	}
</script>
<form method="post" action="/_site/tou_versions">
<input type="hidden" name="tou_id" value="<?=$tou->id?>"/>
<input type="hidden" name="id"/>
<input type="hidden" name="method"/>
<div class="table">
	<div class="tableHead">
		<div class="tableCell">Version</div>
		<div class="tableCell">Status</div>
		<div class="tableCell">Date Created</div>
		<div class="tableCell">Date Published</div>
		<div class="tableCell">Date Retracted</div>
		<div class="tableCell">Action</div>
	</div>
<?php	foreach ($versions as $version) { ?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_site/tou_version?id=<?=$version->id?>"><?=$version->id?></a></div>
		<div class="tableCell"><?=$version->status?></div>
		<div class="tableCell"><?=$version->date_created()?></div>
		<div class="tableCell"><?=$version->date_published()?></div>
		<div class="tableCell"><?=$version->date_retracted()?></div>
		<div class="tableCell">
<?php	if ($version->status == 'NEW') {?>
		<input type="button" name="todo" value="Edit" onclick="edit(<?=$version->id?>);" />
		<input type="button" name="todo" value="Publish" onclick="publish(<?=$version->id?>);" />
<?php	} elseif ($version->status == 'PUBLISHED') { ?>
		<input type="button" name="todo" value="Retract" onclick="retract(<?=$version->id?>);" />
<?php	} ?>		
		</div>
	</div>
<?php	} ?>
</div>
<a href="/_site/tou_version?tou_id=<?=$tou->id?>" class="button">Add Version</a>