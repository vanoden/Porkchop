<?=$page->showAdminPageInfo()?>

<form method="post">
<input type="hidden" name="id" value="<?=$company->id?>" />
<div class="form-field">
  <label for="code">Name</label>
  <input name="name" value="<?=$company->name?>" />
  </div>
<button type="submit" name="btn_submit" value="Update">Update</button>
</form>
