<span class="title">Roles</span>

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

<a href="/_register/role">Create Role</a>
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
