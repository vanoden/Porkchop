<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->
<table class="responsive-table">
  <thead>
    <tr>
      <th class="col-w-30" scope="col">Name</th>
      <th scope="col">Created</th>
      <th scope="col">Registered</th>
      <th scope="col">Expires</th>
      <th scope="col">Company</th>
      <th scope="col">Location</th>
    </tr>
  </thead>
  <tbody>
    <?php	foreach ($domains as $domain) { ?>
    <tr>
      <td data-label="Name"><a href="/_company/domain?name=<?=$domain->name()?>"><?=$domain->name()?></a></td>
      <td data-label="Created"><?=$domain->date_created?></td>
      <td data-label="Registered"><?=$domain->date_registered?></td>
      <td data-label="Expires"><?=$domain->date_expires?></td>
      <td data-label="Company"><?=$domain->company()->name?></td>
      <td data-label="Location"><?=$domain->location()->name?></td>
    </tr>
    <?php } ?>
  </tbody>
</table>
<a class="button" href="/_company/domain" class="input button" id="btn_submit">Add a domain</a>