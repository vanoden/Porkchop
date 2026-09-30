<?=$page->showAdminPageInfo()?>

<form method="post" class="section-grid">
  <input type="hidden" name="id" value="<?=$company->id?>" />
  <div class="form-field">
    <label for="name">Name</label>
    <input type="text" name="name" value="<?=$company->name?>" />
  </div>
  <button type="submit" name="btn_submit" value="Update">Update</button>
</form>
