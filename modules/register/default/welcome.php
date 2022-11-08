<span class="title">Welcome</span>

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

<?php	foreach ($items as $item) { ?>
<div class="welcome_menu_item">
	<span class="welcome_menu_label"><?=$item->text?></span>
	<span class="welcome_menu_description"><?=$item->description?></span>
</div>
<?php	} ?>
