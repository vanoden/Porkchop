<div class="title">Repositories</div>
<table class="body">
<tr><th>Code</th>
	<th>Name</th>
	<th>Type</th>
	<th>Status</th>
</tr>
<?  foreach ($repositories as $repository) { ?>
<tr><td><a href="/_storage/repository?code=<?=$repository->code?>"><?=$repository->code?></a></td>
	<td><?=$repository->name?></td>
	<td><?=$repository->type?></td>
	<td><?=$repository->status?></td>
</tr>
<?  } ?>
</table>