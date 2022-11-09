<span class="title">Domains</span>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<table class="body">
<tr><th>Name</th>
	<th>Created</th>
	<th>Registered</th>
	<th>Expires</th>
	<th>Company</th>
</tr>
<?php	foreach ($domains as $domain) { ?>
<tr><td><a href="/_company/domain?name=<?=$domain->name?>"><?=$domain->name?></a></td>
	<td><?=$domain->date_created?></td>
	<td><?=$domain->date_registered?></td>
	<td><?=$domain->date_expires?></td>
	<td><?=$domain->company->name?></td>
</tr>
<?php } ?>
</table>
<br>
<a href="/_company/domain">Add a domain</a>
