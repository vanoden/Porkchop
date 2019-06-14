<div class="title">Files</div>
<?  if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorString()?></div>
<?  } ?>
<table class="body">
<tr><th>Name</th>
	<th>Mime-Type</th>
	<th>Size (Bytes)</th>
	<th>Date Created</th>
	<th>Owner</th>
	<th>Endpoint</th>
	<th>Read Protect</th>
	<th>Write Protect</th>
</tr>
<?  foreach ($files as $file) { ?>
<tr><td><a href="/_storage/downloadfile?id=<?=$file->id?>"><?=$file->name?></a></td>
	<td><?=$file->mime_type?></td>
	<td><?=$file->size?></td>
	<td><?=$file->date_created?></td>
	<td><?=$file->user->full_name()?></td>
	<td><?=$file->endpoint?></td>
	<td><?=$file->read_protect?></td>
	<td><?=$file->write_protect?></td>
</tr>
<?  } ?>
</table>
