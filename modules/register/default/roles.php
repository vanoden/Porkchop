<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<form action="/_register/role" method="get" class="register-roles-create-form">
	<button type="submit" class="button">Create Role</button>
</form>

<table class="responsive-table responsive-table--banded">
  <thead>
    <tr>
      <th scope="col">Name</th>
      <th scope="col">Description</th>
      <th scope="col" class="register-roles-remove-cell">Remove?</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($roles as $role) { ?>
    <tr>
      <td data-label="Name"><a href="/_register/role/<?=$role->name?>"><?=$role->name?></a></td>
      <td data-label="Description"><?=strip_tags($role->description)?></td>
      <td data-label="Remove?" class="register-roles-remove-cell table-col-align--center">
        <a href="/_register/roles?remove_id=<?=$role->id?>" class="table-icon-link">
          <img class="table-icon" src="/img/icons/icon_tools_trash_active.svg" alt="delete role">
        </a>
      </td>
    </tr>
    <?php } ?>
  </tbody>
</table>