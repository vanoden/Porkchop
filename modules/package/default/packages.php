<div class="title">Packages</div>
<a href="/_package/package">New Package</a>
<table class="body">
<tr><th>Code</th>
	<th>Name</th>
	<th>Status</th>
	<th>License</th>
	<th>Platform</th>
	<th>Owner</th>
	<th>Repository</th>
	<th>Date Created</th>
</tr>
<?php	foreach ($packages as $package) { ?>
<tr><td><a href="/_package/package/<?=$package->code?>"><?=$package->code?></a></td>
	<td><?=$package->name?></td>
	<td><?=$package->status?></td>
	<td><?=$package->license?></td>
	<td><?=$package->platform?></td>
	<td><?=$package->owner->name?></td>
	<td><?=$package->repository->name?></td>
	<td><?=$package->date_created?></td>
</tr>
<?php	} ?>
</table>
