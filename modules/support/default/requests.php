<h2 style="display: inline-block; ">Requests</h2>
<a class="button more" href="/_support/request_new">New Request</a>
<a class="button more" href="/_support/admin_actions">Action Report</a>
<table>
<tr><th>Code</th>
	<th>Date Requested</th>
	<th>Requested By</th>
	<th>Organization</th>
	<th>Type</th>
	<th>Status</th>
</tr>
<?	foreach ($requests as $request) { ?>
<tr><td><a href="/_support/request_detail/<?=$request->code?>"><?=$request->code?></a></td>
	<td><?=$request->date_request?></td>
	<td><?=$request->customer->first_name?> <?=$request->customer->last_name?></td>
	<td><?=$request->customer->organization->name?></td>
	<td><?=$request->type?></td>
	<td><?=$request->status?></td>
</tr>
<?	} ?>
</table>