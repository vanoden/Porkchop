<?=$page->showAdminPageInfo()?>
<style>
	table {
		margin: 1px;
		border: 1px solid gray;
		border-collapse: collapse;
		color: black;
		background-color: white;
	}
	table th {
		text-align: left;
		white-space: nowrap;
		padding-right: 10px;
		padding-left: 5px;
	}
	table td {
		white-space: nowrap;
		border: 1px solid gray;
		padding-right: 10px;
		padding-left: 5px;
	}
</style>
Showing <?=count($logRecords)?> log records
<?php if ($log->count() == 0) { ?>
	<p>No log records found.</p>
<?php } else { ?>
	<table class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>ID</th>
				<th>Timestamp</th>
				<th>Remote</th>
				<th>Function</th>
				<th>Client</th>
				<th>Server</th>
				<th>Length</th>
				<th>Session</th>
				<th>Content</th>
				<th>Success</th>
				<th>Error</th>
			</tr>
		</thead>
		<tbody>
<?php
		foreach ($logRecords as $record) {	
			$function = $record->functionName();
			$client = $record->clientID();
			$server = $record->serverID();
			$length = $record->contentLength();
			$session = $record->sessionCode();
			$sessionBytes = $record->sessionCodeDebug();
			$contentBytes = $record->contentDebug();
			$message = $record->message();
			$message->parse($record->contentBytes(),$length);
			if ($message->readable() != "") {
				$contentBytes = '"'.$message->readable().'"';
			}
			//print_r($record->message());
?>
			<tr>
				<td><?=$record->id()?></td>
				<td><?=$record->timestamp()?></td>
				<td><?=$record->remoteAddress()?></td>
				<td><?=$function?> [<?=implode(",",$record->functionBytes())?>]</td>
				<td><?=$client?> [<?=implode(",",$record->clientBytes())?>]</td>
				<td><?=$server?> [<?=implode(",",$record->serverBytes())?>]</td>
				<td><?=$length?> [<?=implode(",",$record->lengthBytes())?>]</td>
				<td><?=$sessionBytes?></td>
				<td><?=$contentBytes?></td>
				<td><?php if ($record->success()) { ?><span class="badge badge-success">Yes</span><?php } else { ?><span class="badge badge-danger">No</span><?php } ?></td>
				<td><?=$record->error()?></td>
			</tr>
<?php
		}
?>
		</tbody>
	</table>
<?php } ?>