<?=$page->showAdminPageInfo()?>
<a href="/_site/header" class="button">Add Header</a>
<div class="tableBody min-tablet">
    <div class="tableRowHeader">
        <div class="tableCell" style="width: 25%;">Name</div>
        <div class="tableCell" style="width: 75%;">Value</div>
    </div>
<?php	foreach ($headers as $header) { ?>
	<div class="tableRow">
		<div class="tableCell" style="width: 25%;"><a href="/_site/header?id=<?=$header->id()?>"><?=$header->name()?></a></div>
		<div class="tableCell" style="width: 75%;"><?=$header->value()?></div>
	</div>
<?php	} ?>
</div>