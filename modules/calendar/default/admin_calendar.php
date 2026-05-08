<?=$page->showAdminPageInfo()?>

<!-- Select Calendar -->
<form method="post" action="admin_calendar">
<select name="calendar_id">
<?php	foreach ($calendars as $select_calendar) { ?>
	<option value="<?=$select_calendar->id?>"><?=$select_calendar->name?></option>
<?php	} ?>
</select>
</form>

<?php if ($calendar->id) { ?>
<!-- Calendar Details -->

<!-- Add Event Form -->
<form method="post" action="admin_calendar">
<input type="hidden" name="calendar_id" value="<?=$calendar->id?>" />
<input type="hidden" name="csrfToken" value="<?=$session->csrfToken?>" />

