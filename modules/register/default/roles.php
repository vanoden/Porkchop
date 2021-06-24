<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Name</div>
		<div class="tableCell">Description</div>
	</div>
<?php	foreach ($roles as $role) { ?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_register/role/<?=$role->name?>"><?=$role->name?></a></div>
		<div class="tableCell"><?=$role->description?></div>
	</div>
<?php	} ?>
</div>
