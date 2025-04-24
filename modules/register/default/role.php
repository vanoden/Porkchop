<script>
// check or uncheck all boxes for ease of manage privileges
function checkUncheck(isChecked) {
    document.querySelectorAll('input[type=checkbox]').forEach(el => el.checked = isChecked);
}
</script>


<!-- Page Header -->
<?=$page->showTitle()?>
<?=$page->showBreadcrumbs()?>
<?=$page->showMessages()?>
<!-- End Page Header -->

<form method="post" action="/_register/role">
  <input type="hidden" name="name" value="<?=$role->name?>" />
  <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
  <div>
    <label>Role Name</label>
    <?php if ($role->id) { ?>
      <span class="value"><?=$role->name?></span>
    <?php } else { ?>
      <input type="text" name="name" value="" />
    <?php } ?>
  </div>

  <div>
    <label>Description</label>
    <input type="text" name="description" value="<?=strip_tags($role->description)?>" />
    <input type="hidden" name="id" value="<?=$role->id?>">
  </div>

  <div id="rolePrivilegesContainer">

    <div id="search_container">
      <label>Privileges</label>
      <a class="button" onclick="checkUncheck(true)" style="cursor: pointer;">&#10003; Check All</a>
      <a class="button" onclick="checkUncheck(false)" style="cursor: pointer;">&#10006; Uncheck All</a>
      <a class="button secondary" href="/_register/privileges" style="cursor: pointer;">Manage</a>
    </div>


	  <div class="tableBody">

      <div class="tableRowHeader">
        <div class="tableCell" style="width: 10%; text-align: center;">Select</div>
        <div class="tableCell" style="width: 30%;">Privilege Module</div>
        <div class="tableCell">Privilege Name</div>
      </div>

      <?php	foreach ($privileges as $privilege) { ?>
      <div class="tableRow">
        <div class="tableCell" style="text-align: center;"><input type="checkbox" name="privilege[<?=$privilege->id?>]" value="1"<?php if ($role->has_privilege($privilege->id)) print " checked";?>></div>
        <div class="tableCell"><?=$privilege->module?></div>
        <div class="tableCell"><?=$privilege->name?></div>
      </div>
      <?php	} ?>

    </div>

  </div>

  <!-- entire page button submit -->
  <div id="submit-button-container" class="tableBody min-tablet">
    <div class="tableRow button-bar">
      <?php if (isset($role->id)) { ?>
        <input type="submit" name="btn_submit" class="button" value="Update">
      <?php } else { ?>
        <input type="submit" name="btn_submit" class="button" value="Create">
      <?php } ?>
    </div>
  </div>
</form>