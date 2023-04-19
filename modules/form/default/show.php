<?= $page->showBreadcrumbs(); ?>
<?= $page->showTitle(); ?>
<?= $page->showMessages(); ?>

<form action="<?=$form->action?>">
<ul>
	<li><?=$form->title?></li>
	<li><?=$form->description?></li>
<?php 	foreach ($questions as $question) { ?>
<?php 		if ($question->type == "text") { ?>
	<li><span class="label"><?=$question->text?></span>
		<input type="text" name="answer[<?=$question->id?>]" /></li>
<?php 		} elseif ($question->type == "textarea") { ?>
	<li><span class="label"><?=$question->text?></span>
		<textarea name="answer[<?=$question->id?>]"></textarea></li>
<?php 		} elseif ($question->type == "select") { ?>
	<li><span class="label"><?=$question->text?></span>
		<select name="answer[<?=$question->id?>]">
<?php 			foreach ($question->options as $option) { ?>
			<option value="<?=$option->id?>"><?=$option->text?></option>
<?php 			} ?>
		</select></li>
<?php 		} elseif ($question->type == "checkbox") { ?>
	<li><span class="label"><?=$question->text?></span>
	<?php 			foreach ($question->options as $option) { ?>
		<input type="checkbox" name="answer[<?=$question->id?>]" value="1" /></li>
	<?php 			} ?>
<?php 		} elseif ($question->type == "radio") { ?>
	<li><ul>
	<?php 			foreach ($question->options as $option) { ?>
	<li><span class="label"><?=$question->text?></span>
		<input type="radio" name="answer[<?=$question->id?>]" value="1" /></li>
	<?php 			} ?>
	</ul></li>
<?php 		} elseif ($question->type == "submit") { ?>
	<li><input type="submit" name="submit" value="<?=$question->text?>" /></li>
<?php 		} ?>
<?php 	} ?>
</ul>

</form>