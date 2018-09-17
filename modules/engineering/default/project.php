<div style="width: 756px;">
<form name="project_form" action="/_engineering/project" method="post">
<input type="hidden" name="project_id" value="<?=$project->id?>" />
<div class="breadcrumbs">
<a class="breadcrumb" href="/_engineering/home">Engineering</a>
<a class="breadcrumb" href="/_engineering/projects">Projects</a>
</div>
<div class="title">Engineering Project</div>
<?	if ($page->error) { ?>
<div class="form_error"><?=$page->error?></div>
<?	}
	if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?	} ?>
<div class="container_narrow">
	<div class="label">Code</div>
	<input type="text" name="code" class="value input" value="<?=$form['code']?>" />
</div>
<div class="container_narrow">
	<div class="label">Title</div>
	<input type="text" name="title" class="value input" style="width: 240px" value="<?=$form['title']?>" />
</div>
<div class="container_narrow">
	<div class="label">Manager</div>
	<select name="manager_id" class="value input" style="width: 240px">
		<option value="">Unassigned</option>
<?	foreach ($managers as $manager) { ?>
		<option value="<?=$manager->id?>"<? if ($manager->id == $project->manager->id) print " selected"; ?>><?=$manager->code?></option>
<?	} ?>
</div>
<div class="container">
	<div class="label">Description</div>
	<textarea name="description" style="width: 700px; height: 300px;"><?=$form['description']?></textarea>
</div>
<div class="container">
	<input type="submit" name="btn_submit" class="button" value="Submit">
</div>
</form>
</div>
