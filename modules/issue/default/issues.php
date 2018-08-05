<a href="/_issue/issue">Report an Issue</a>
<div>Existing Issues</div>
<table class="body">
<tr><th class="label">Date Reported</th>
	<th class="label">Title</th>
	<th class="label">Reported By</th>
	<th class="label">Status</td>
	<th class="label">Priority</td>
</tr>
<?php
	foreach ($issues as $issue) {
?>
<tr><td class="value"><?=$issue->date_reported?></td>
	<td class="value"><a href="/_issue/issue/<?=$issue->code?>"><?=$issue->title?></a></td>
	<td class="value"><?=$issue->user_reported->code?></td>
	<td class="value"><?=$issue->status?></td>
	<td class="value"><?=$issue->priority?></td>
</tr>
<?php
	}
?>
</table>