<?= $page->showBreadcrumbs(); ?>
<?= $page->showTitle(); ?>
<?= $page->showMessages(); ?>

<ul>
	<li>
		<span class="label">Title</span>
		<input type="text" name="title" class="value" value="<?=$form->title?>" />
	</li>
	<li>
		<span class="label">Action</span>
		<input type="text" name="action_" class="value" value="<?=$form->action?>" />
	</li>
	<li>
		<span class="label">Instructions</span>
		<textarea name="instructions" class="value"><?=$form->instructions?></textarea>
	</li>
</ul>