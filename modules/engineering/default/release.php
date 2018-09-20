<div style="width: 756px;">
<form name="release_form" action="/_engineering/release" method="post">
<input type="hidden" name="release_id" value="<?=$release->id?>" />
<div class="breadcrumbs">
<a class="breadcrumb" href="/_engineering/home">Engineering</a>
<a class="breadcrumb" href="/_engineering/releases">Releases</a>
</div>
<div class="title">Engineering Release</div>
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
	<div class="label">Status</div>
	<select name="status" class="value input">
		<option value="new"<? if ($form['status'] == "NEW") print " selected"; ?>>New</option>
		<option value="testing"<? if ($form['status'] == "TESTING") print " selected"; ?>>Testing</option>
		<option value="released"<? if ($form['status'] == "RELEASED") print " selected"; ?>>Released</option>
	</select>
</div>
<div class="container_narrow">
	<div class="label">Date Scheduled</div>
	<input type="text" name="date_scheduled" class="value input" value="<?=$form['date_scheduled']?>" />
</div>
<div class="container_narrow">
	<div class="label">Date Released</div>
	<input type="text" name="date_released" class="value input" value="<?=$form['date_released']?>" />
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
<?	if ($release->id) { ?>
<table style="width: 756px;">
<tr><td class="label">Title</td>
	<td class="label">Project</td>
	<td class="label">Status</td>
</tr>
<?	foreach ($tasks as $task) { 
		$project = $task->project();
?>
<tr><td class="value <?=$greenbar?>"><a href="/_engineering/task/<?=$task->code?>"><?=$task->title?></a></td>
	<td class="value <?=$greenbar?>"><?=$project->title?></td>
	<td class="value <?=$greenbar?>"><?=$task->status?></td>
</tr>
<?		if (! $greenbar) $greenbar = 'greenbar';
		else $greenbar = '';
	}
?>
</table>
<?	} ?>
