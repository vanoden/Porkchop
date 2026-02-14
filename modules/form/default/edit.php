<?= $page->showAdminPageInfo(); ?>

<form method="post">
<input type="hidden" name="id" value="<?=$form->id?>" />
<div class="section">
	<span class="label">Code</span>
	<input type="text" name="code" value="<?=$form->code?>" />
	<span class="label">Title</span>
	<input type="text" name="title" value="<?=$form->title?>" />
	<span class="label">Description</span>
	<input type="text" name="description" value="<?=strip_tags($form->description)?>" />
	<span class="label">Action</span>
	<input type="text" name="action" value="<?=$form->action?>" />
	<span class="label">Method</span>
	<select name="method">
		<option value="post"<?php if ($form->method == "post") print " selected";?>>POST</option>
		<option value="get"<?php if ($form->method == "get") print " selected";?>>GET</option>
	</select>
	<span class="label">Instructions</span>
	<textarea name="instructions"><?=$form->instructions?></textarea>
</div>
<div class="section">
	<div class="tableBody">
		<div class="tableRowHeader">
			<div class="tableCell">Type</div>
			<div class="tableCell">Question</div>
			<div class="tableCell">Prompt</div>
			<div class="tableCell">Required</div>
		</div>
<?php	foreach($questions as $question) { ?>
		<div class="tableRow">
			<div class="tableCell">
				<select name="type[<?=$question->id?>]">
					<option value="text"<?php if ($question->type == "text") print " selected";?>>Text</option>
					<option value="textarea"<?php if ($question->type == "textarea") print " selected";?>>Textarea</option>
					<option value="select"<?php if ($question->type == "select") print " selected";?>>Select</option>
					<option value="checkbox"<?php if ($question->type == "checkbox") print " selected";?>>Checkbox</option>
					<option value="radio"<?php if ($question->type == "radio") print " selected";?>>Radio</option>
					<option value="submit"<?php if ($question->type == "submit") print " selected";?>>Submit</option>
					<option value="hidden"<?php if ($question->type == "hidden") print " selected";?>>Hidden</option>
				</select>
			</div>
			<div class="tableCell"><input type="text" name="text[<?=$question->id?>]" value="<?=strip_tags($question->text)?>" /></div>
			<div class="tableCell"><input type="text" name="prompt[<?=$question->id?>]" value="<?=$question->prompt?>" /></div>
			<div class="tableCell"><input type="checkbox" name="required[<?=$question->id?>]" value="1"<?php if ($question->required) print " checked";?> /></div>
		</div>
<?php	} ?>
		<div class="tableRow">
			<div class="tableCell">
				<select name="type_new">
					<option value="text"<?php if ($question->type == "text") print " selected";?>>Text</option>
					<option value="textarea"<?php if ($question->type == "textarea") print " selected";?>>Textarea</option>
					<option value="select"<?php if ($question->type == "select") print " selected";?>>Select</option>
					<option value="checkbox"<?php if ($question->type == "checkbox") print " selected";?>>Checkbox</option>
					<option value="radio"<?php if ($question->type == "radio") print " selected";?>>Radio</option>
					<option value="submit"<?php if ($question->type == "submit") print " selected";?>>Submit</option>
				</select>
			</div>
			<div class="tableCell"><input type="text" name="text_new" value="" /></div>
			<div class="tableCell"><input type="text" name="prompt_new" value="" /></div>
			<div class="tableCell"><input type="checkbox" name="required_new" value="1" /></div>
		</div>
	</div>
</div>
<div class="section">
	<input type="submit" name="submit" value="Save" />
</div>
</form>