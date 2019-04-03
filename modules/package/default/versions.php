<div class="title">Versions</div>
<table class="body">
<tr><th>Version</th>
    <th>Status</th>
    <th>User</th>
    <th>Date Created</th>
</tr>
<?  foreach ($versions as $version) { ?>
<tr><td><?=$version->version()?></td>
    <td><?=$version->status?></td>
    <td><?=$version->user->full_name()?></td>
    <td><?=$version->date_created?></td>
</tr>
<?  } ?>
</table>